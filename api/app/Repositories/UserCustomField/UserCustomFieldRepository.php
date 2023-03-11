<?php

namespace App\Repositories\UserCustomField;

use App\Models\UserCustomField;
use App\Repositories\UserCustomField\UserCustomFieldInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class UserCustomFieldRepository implements UserCustomFieldInterface
{
    /**
     * User custom field
     *
     * @var App\Models\UserCustomField
     */
    private $field;

    /**
     * Create a new repository instance.
     *
     * @param App\Models\UserCustomField $field
     * @return void
     */
    public function __construct(UserCustomField $field)
    {
        $this->field = $field;
    }

    /**
     * Get listing of user custom fields
     *
     * @param Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function userCustomFieldList(Request $request): LengthAwarePaginator
    {
        $customFields = $this->field;
        if ($request->has('search')) {
            $customFields = $customFields->where('name', 'like', '%' . $request->input('search') . '%')
                ->orWhere('internal_note', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->has('mandatory') && $request->input('mandatory') !== '') {
            $customFields = $customFields->whereRaw("BINARY `is_mandatory` = ?", [$request->input('mandatory')]);
        }

        if ($request->has('type')) {
            $customFields = $customFields->whereIn('type', $request->input('type'));
        }

        if ($request->has('order')) {
            $orderDirection = $request->input('order', 'asc');
            $customFields = $customFields->orderBy('order', $orderDirection);
        }

        return $customFields->paginate($request->perPage);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param array $request
     * @return App\Models\UserCustomField
     */
    public function store(array $request): UserCustomField
    {
        return $this->field->create($request);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  array  $request
     * @param  int  $id
     * @return App\Models\UserCustomField
     */
    public function update(array $request, int $id): UserCustomField
    {
        $customField = $this->field->findOrFail($id);
        $customField->update($request);
        return $customField;
    }

    /**
     * Find user custom field in storage.
     *
     * @param  int  $id
     * @return App\Models\UserCustomField
     */
    public function find(int $id): UserCustomField
    {
        return $this->field->findOrFail($id);
    }

    /**
     * Returns value of the highest order.
     *
     * @return int|null
     */
    public function findMaxOrder()
    {
        return $this->field->max('order');
    }

    /**
     * Returns value of the smallest order.
     *
     * @param mixed $ids
     *
     * @return mixed
     */
    public function findMinOrder($ids = null)
    {
        if (empty($ids)) {
            return $this->field->min('order');
        } elseif (is_array($ids)) {
            return $this->field->whereIn('field_id', $ids)->min('order');
        }
        return $this->field->select('order')->findOrFail($ids);
    }

    /**
     * Selects records after certain order.
     *
     * @param int $order
     *
     * @return Collection
     */
    public function findAfterMinOrder($order)
    {
        $fields = $this->field->where('order', '>', $order);
        $fields = $fields->orderBy('order', 'asc');
        return $fields->lockForUpdate()->get();
    }

    /**
     * Selects records between certain order.
     *
     * @param integer $currentOrder
     * @param integer $requestOrder
     *
     * @return Collection
     */
    public function findBetweenOrder($currentOrder, $requestOrder): Collection
    {
        // where condition for $currentOrder > $requestOrder
        $fields = $this->field->where('order', '>=', $requestOrder);
        $fields = $fields->where('order', '<', $currentOrder);

        if ($currentOrder < $requestOrder) {
            $fields = $this->field->where('order', '>', $currentOrder);
            $fields = $fields->where('order', '<=', $requestOrder);
        }

        $fields = $fields->orderBy('order', 'asc');

        return $fields->lockForUpdate()->get();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->field->deleteCustomField($id);
    }

    /**
     * Remove the array of resources from storage.
     *
     * @param  array  $ids
     * @return int
     */
    public function deleteMultiple(array $ids)
    {
        $this->field->findOrFail($ids);
        return $this->field->destroy($ids);
    }

    /**
     * Get listing of user custom fields
     *
     * @param Illuminate\Http\Request $request
     * @return Illuminate\Support\Collection
     */
    public function getUserCustomFields(Request $request): Collection
    {
        return $this->field->orderBy('order', 'asc')->get();
    }
}
