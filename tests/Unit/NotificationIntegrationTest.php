<?php

namespace Tests\Unit;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NotificationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function notification_model_creates_successfully_with_morph_fields()
    {
        $user = User::factory()->create();

        $notification = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'test_notification',
            'data' => ['key' => 'value'],
        ]);

        $this->assertNotNull($notification->id);
        $this->assertEquals(User::class, $notification->notifiable_type);
        $this->assertEquals($user->id, $notification->notifiable_id);
        $this->assertEquals('test_notification', $notification->type);
    }

    #[Test]
    public function notification_relationship_can_access_notifiable_user()
    {
        $user = User::factory()->create();

        $notification = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'test_notification',
            'data' => [],
        ]);

        $retrievedNotifiable = $notification->notifiable;

        $this->assertNotNull($retrievedNotifiable);
        $this->assertInstanceOf(User::class, $retrievedNotifiable);
        $this->assertEquals($user->id, $retrievedNotifiable->id);
    }

    #[Test]
    public function user_can_have_multiple_notifications()
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            Notification::create([
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'type' => "notification_type_{$i}",
                'data' => ['index' => $i],
            ]);
        }

        $notifications = $user->notifications;

        $this->assertCount(5, $notifications);
        $this->assertTrue($notifications->pluck('type')->contains('notification_type_0'));
        $this->assertTrue($notifications->pluck('type')->contains('notification_type_4'));
    }

    #[Test]
    public function notifications_can_be_filtered_by_type()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user1->id,
            'type' => 'leave_approved',
            'data' => [],
        ]);

        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user1->id,
            'type' => 'leave_rejected',
            'data' => [],
        ]);

        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user2->id,
            'type' => 'leave_approved',
            'data' => [],
        ]);

        $approvedNotifications = Notification::where('type', 'leave_approved')->get();

        $this->assertCount(2, $approvedNotifications);
    }

    #[Test]
    public function notification_data_field_stores_json_correctly()
    {
        $user = User::factory()->create();
        $testData = [
            'request_id' => 'REQ-12345',
            'reason' => 'Sakit Kepala',
            'duration' => 3,
        ];

        $notification = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'leave_approved',
            'data' => $testData,
        ]);

        $retrieved = Notification::find($notification->id);

        $this->assertEquals($testData, $retrieved->data);
        $this->assertEquals('Sakit Kepala', $retrieved->data['reason']);
    }

    #[Test]
    public function notification_timestamps_are_set_correctly()
    {
        $user = User::factory()->create();

        $notification = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'test_notification',
            'data' => [],
        ]);

        $this->assertNotNull($notification->created_at);
        $this->assertNotNull($notification->updated_at);
        $this->assertNull($notification->read_at);
    }

    #[Test]
    public function notification_can_be_marked_as_read()
    {
        $user = User::factory()->create();

        $notification = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'test_notification',
            'data' => [],
        ]);

        $this->assertNull($notification->read_at);

        $notification->update(['read_at' => now()]);
        $notification->refresh();

        $this->assertNotNull($notification->read_at);
    }

    #[Test]
    public function notification_uuid_is_unique()
    {
        $user = User::factory()->create();

        $notification1 = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'notification_1',
            'data' => [],
        ]);

        $notification2 = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'type' => 'notification_2',
            'data' => [],
        ]);

        $this->assertNotEquals($notification1->id, $notification2->id);
        $this->assertTrue(\Ramsey\Uuid\Uuid::isValid($notification1->id));
        $this->assertTrue(\Ramsey\Uuid\Uuid::isValid($notification2->id));
    }

    #[Test]
    public function multiple_users_can_have_same_notification_type()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $user3 = User::factory()->create();

        foreach ([$user1, $user2, $user3] as $user) {
            Notification::create([
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'type' => 'system_announcement',
                'data' => ['version' => '1.0'],
            ]);
        }

        $announcements = Notification::where('type', 'system_announcement')->get();

        $this->assertCount(3, $announcements);
    }
}

