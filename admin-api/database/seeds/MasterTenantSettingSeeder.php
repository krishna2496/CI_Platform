<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class MasterTenantSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = [            
            [
                'title' => 'Total Votes In The Platform',
                'description' => 'testing description here',
                'key' => 'total_votes',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'skills enabled',
                'description' => 'User profile edit page - Add new skills (Allow the user to add or manage his skills. If enabled open modal)',
                'key' => 'skills_enabled',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'stories enabled',
                'description' => 'Story module - Story listing page, story detail page and ability to add a new story',
                'key' => 'stories_enabled',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'news enabled',
                'description' => 'News page',
                'key' => 'news_enabled',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'themes enabled',
                'description' => 'Themes enabled/disabled for mission',
                'key' => 'themes_enabled',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'policies enabled',
                'description' => 'Policy pages',
                'key' => 'policies_enabled',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'sorting missions',
                'description' => 'Sorting missions',
                'key' => 'sorting_missions',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'quick access filters',
                'description' => 'Quick access filters on platform landing page',
                'key' => 'quick_access_filters',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'total hours volunteered in platform',
                'description' => 'Display Total Hours volunteered in the platform - Platform landing page',
                'key' => 'total_hours_volunteered_in_platform',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'total missions in platform',
                'description' => 'Display - Total missions in the platform - Platform landing page',
                'key' => 'total_missions_in_platform',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'time credit mission',
                'description' => 'Display if the mission support time credit volunteering ',
                'key' => 'time_credit_mission',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'user can submit mission',
                'description' => 'User can submit a mission',
                'key' => 'user_can_submit_mission',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'share mission facebook',
                'description' => 'Enable/disable mission share on facebook',
                'key' => 'share_mission_facebook',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'share mission twitter',
                'description' => 'Enable/disable mission share on twitter',
                'key' => 'share_mission_twitter',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'recent volunteers',
                'description' => 'View who applied / Recent volunteers',
                'key' => 'recent_volunteers',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'mission ratings',
                'description' => 'Mission ratings',
                'key' => 'mission_ratings',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'invite colleague',
                'description' => 'Invite colleague',
                'key' => 'invite_colleague',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'mission comments',
                'description' => 'Comment section on mission detail page',
                'key' => 'mission_comments',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'show goal of mission',
                'description' => 'Mission detail page - display goal of the mission - (Display the goal of the mission in a box) ',
                'key' => 'show_goal_of_mission',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'show current status of mission',
                'description' => 'Mission detail page - display current status of the mission (Display the remaining of the mission in a box)',
                'key' => 'show_current_status_of_mission',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'show remaining data to achieve goal',
                'description' => 'Mission detail page - display the remaining data to achieve the goal (Display the remaining data of the mission in a box)',
                'key' => 'show_remaining_data_to_achieve_goal',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'volunteering hours auto approved',
                'description' => 'Volunteering hours timesheet for time mission - Timesheet submit for approval or auto-approved',
                'key' => 'volunteering_hours_auto_approved',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'volunteering goal auto approved',
                'description' => 'Volunteering action timesheet for goal mission - Timesheet submit for approval or auto-approved',
                'key' => 'volunteering_goal_auto_approved',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'mission comment auto approved',
                'description' => 'All comments have to be approved by the admin or automatically approved',
                'key' => 'mission_comment_auto_approved',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'is story auto approved',
                'description' => 'All stories have to be approved by the admin (or automatically approved. Stories can be disabled by admin',
                'key' => 'is_story_auto_approved',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
            [
                'title' => 'country selection',
                'description' => 'Country selection is enabled/disabled',
                'key' => 'country_selection',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
			[
                'title' => 'Email notification for invite colleague',
                'description' => 'Enable/disable email notification for invite colleague.',
                'key' => 'email_notification_invite_colleague',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
			[
                'title' => 'Enable/disable document upload for timesheet entry',
                'description' => 'Enable/disable document upload for timesheet entry.',
                'key' => 'timesheet_document_upload',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
			[
                'title' => 'Contact us enabled',
                'description' => 'Enable/disable contact us/report a bug link in platform.',
                'key' => 'contact_us_enabled',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
			[
                'title' => 'Enable/disable mission ratings for volunteers',
                'description' => 'Enable/disable mission ratings so only volunteers that have been approved to the mission can rate the mission.',
                'key' => 'mission_rating_volunteer',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ],
			[
                'title' => 'Related missions',
                'description' => 'Hide/Show related mission on mission detail page.',
                'key' => 'related_missions',
                "created_at" => Carbon::now(),
                "updated_at" => Carbon::now()
            ]
			

        ];
    
        foreach ($items as $item) {            
            \DB::table('tenant_setting')->insert($item);
        }
    }
}
