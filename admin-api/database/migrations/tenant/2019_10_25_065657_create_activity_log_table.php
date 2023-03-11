<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activity_log', function (Blueprint $table) {
            $table->bigIncrements('activity_log_id');
            $table->enum('type', ['AUTH','USERS','MISSION','COMMENT','MESSAGE','USERS_CUSTOM_FIELD','USER_PROFILE','USER_PROFILE_IMAGE','NEWS_CATEGORY','NEWS','VOLUNTEERING_TIMESHEET','VOLUNTEERING_TIMESHEET_DOCUMENT','SLIDER','STYLE_IMAGE','STYLE','TENANT_OPTION','TENANT_SETTINGS','FOOTER_PAGE','POLICY_PAGE','MISSION_THEME','SKILL','USER_SKILL','USER_COOKIE_AGREEMENT','GOAL_TIMESHEET','TIME_TIMESHEET','TIME_MISSION_TIMESHEET','GOAL_MISSION_TIMESHEET','STORY','MISSION_COMMENTS','STORY_IMAGE','STORY_VISITOR','NOTIFICATION_SETTING','NOTIFICATION','AVAILABILITY','COUNTRY']);
            $table->enum('action', ['CREATED','UPDATED','DELETED','INVITED','SUBMIT_FOR_APPROVAL','APPROVED','DECLINED','LOGIN','ADD_TO_FAVOURITE','REMOVE_FROM_FAVOURITE','RATED','COMMENT_ADDED','COMMENT_UPDATED','COMMENT_DELETED','MISSION_APPLICATION_CREATED','MISSION_APPLICATION_STATUS_CHANGED','PASSWORD_RESET_REQUEST','PASSWORD_CHANGED','PASSWORD_RESET','LINKED','UNLINKED','ACCEPTED','EXPORT','COPIED','COUNTED','READ','ACTIVATED','DEACTIVATED','CLEAR_ALL','PASSWORD_UPDATED']);
            $table->string('object_class', 500);
            $table->bigInteger('object_id')->nullable();
            $table->text('object_value')->nullable();
            $table->dateTime('date')->useCurrent();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('user_type', ['API', 'REGULAR']);
            $table->string('user_value', 255);            
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('user_id')->on('user')->onDelete('CASCADE')->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_log');
    }
}
