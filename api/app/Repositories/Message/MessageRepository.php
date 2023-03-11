<?php

namespace App\Repositories\Message;

use App\Models\Message;
use App\Repositories\Message\MessageInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class MessageRepository implements MessageInterface
{
    /**
     *
     * @var App\Models\Message
     */
    private $message;

    /**
     * Create a new message repository instance.
     *
     * @param  App\Models\Message $message
     * @return void
     */
    public function __construct(
        Message $message
    ) {
        $this->message = $message;
    }

    /**
     * Store message details
     *
     * @param \Illuminate\Http\Request $request
     * @param int $messageSentFrom
     * @return array
     */
    public function store(Request $request, int $messageSentFrom): array
    {
        $adminName =  !empty($request->admin) ? $request->admin : null;
        $messageIds = [];
        // found message from admin
        $message = ['sent_from' => $messageSentFrom,
                    'admin_name' => $adminName,
                    'subject' => $request->subject,
                    'message' => $request->message
                    ];
        if ($messageSentFrom == config('constants.message.send_message_from.admin')) {
            $isAnonymous = !empty($request->admin) ?
                       config('constants.message.not_anonymous') :
                       config('constants.message.anonymous');
            $now = Carbon::now()->toDateTimeString();
            foreach ($request->user_ids as $userId) {
                $messageDataArray = [
                    'user_id' => $userId,
                    'is_read' => config('constants.message.unread'),
                    'is_anonymous' => $isAnonymous,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $messageData = $this->message->create(array_merge($message, $messageDataArray));
                array_push($messageIds, ['message_id' => $messageData->message_id, 'user_id' => $userId]);
            }
        } else {
            $messageDataArray = array(
                'user_id' => $request->auth->user_id,
                'is_read' => config('constants.message.unread'),
            );
            $messageDataArray = array_merge($message, $messageDataArray);
            $messageData = $this->message->create($messageDataArray);
            array_push($messageIds, $messageData->message_id);
        }
        return $messageIds;
    }

    /**
     * Display a listing of specified resources with pagination.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $sentFrom
     * @param Array $userIds
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getUserMessages(
        Request $request,
        int $sentFrom,
        array $userIds = []
    ): LengthAwarePaginator {
        $userMessageQuery = $this->message->select('*')->with(['user' => function ($query) {
            $query->select('user_id', 'first_name', 'last_name');
        }]);
        if (config('constants.message.send_message_from.all') !== $sentFrom) {
            $userMessageQuery->where('sent_from', $sentFrom);
        }
        $userMessageQuery->when(
            $userIds,
            function ($query, $userIds) {
                return $query->whereIn('user_id', $userIds);
            }
        )->orderBy('created_at', 'desc');
        return $userMessageQuery->paginate($request->perPage);
    }


    /**
     * Remove message details.
     *
     * @param int $messageId
     * @param int $sentFrom
     * @param int $userId
     * @return bool
     */
    public function delete(int $messageId, int $sentFrom, int $userId = null): bool
    {
        return $this->message->where(
            [
                'message_id' => $messageId,
                'sent_from' => $sentFrom
            ]
        ) ->when(
            $userId,
            function ($query, $userId) {
                return $query->where('user_id', $userId);
            }
        )->firstOrFail()->delete();
    }
    
    /**
     * Read message.
     *
     * @param int $messageId
     * @param int $userId | null
     * @param int $sentFrom
     * @return App\Models\Message
     */
    public function readMessage(int $messageId, int $userId = null, int $sentFrom): Message
    {
        $messageDetails = $this->message->findMessage($messageId, $userId, $sentFrom);
        $messageDetails->update(['is_read' => config('constants.message.read')]);
        return $messageDetails;
    }

    /**
     * Count unread messages.
     *
     * @param int $userId
     * @return Illuminate\Database\Eloquent\Collection
     */
    public function getUnreadMessageCount(int $userId): Collection
    {
        $messageUnreadCount = $this->message->selectRaw('COUNT(is_read) as unread')
        ->where([
            'is_read' => config('constants.message.unread'),
            'sent_from' => config('constants.message.send_message_from.admin'),
            'user_id' => $userId
        ])->get();
        return $messageUnreadCount;
    }

    /**
     * Get message detail
     *
     * @param int $messageId
     * @return App\Models\Message
     */
    public function getMessageDetail(int $messageId): Message
    {
        return $this->message->withTrashed()->find($messageId);
    }
}
