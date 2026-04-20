<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationMorphFieldsTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function notification_creates_with_morph_fields()
    {
        // Create notification with explicit morph fields
        $notification = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'test_notification',
            'data' => [],
        ]);

        // Verify morph fields are set
        $this->assertEquals(User::class, $notification->notifiable_type);
        $this->assertEquals($this->user->id, $notification->notifiable_id);
    }

    /** @test */
    public function notification_stores_all_fields_correctly()
    {
        // Create notification with all available fields
        $notification = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'test_notification',
            'data' => ['key' => 'value'],
        ]);

        // Verify all fields are preserved
        $this->assertEquals(User::class, $notification->notifiable_type);
        $this->assertEquals($this->user->id, $notification->notifiable_id);
        $this->assertEquals('test_notification', $notification->type);
        $this->assertEquals(['key' => 'value'], $notification->data);
    }

    /** @test */
    public function notification_morph_relationship_works_correctly()
    {
        $notification = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'test_notification',
            'data' => [],
        ]);

        // Verify polymorphic relationship
        $this->assertNotNull($notification->notifiable);
        $this->assertInstanceOf(User::class, $notification->notifiable);
        $this->assertEquals($this->user->id, $notification->notifiable->id);
    }

    /** @test */
    public function notification_can_be_retrieved_by_notifiable_relationship()
    {
        $notification = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'test_notification',
            'data' => [],
        ]);

        $retrieved = $this->user->notifications()->first();

        $this->assertNotNull($retrieved);
        $this->assertEquals($notification->id, $retrieved->id);
    }

    /** @test */
    public function notification_handles_uuid_correctly()
    {
        $notification = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'test_notification',
            'data' => [],
        ]);

        // Verify UUID is generated
        $this->assertNotNull($notification->id);
        $this->assertTrue(\Ramsey\Uuid\Uuid::isValid($notification->id));
    }

    /** @test */
    public function multiple_notifications_can_belong_to_same_user()
    {
        $notifications = [
            Notification::create([
                'notifiable_type' => User::class,
                'notifiable_id' => $this->user->id,
                'type' => 'leave_approved',
                'data' => [],
            ]),
            Notification::create([
                'notifiable_type' => User::class,
                'notifiable_id' => $this->user->id,
                'type' => 'business_trip_approved',
                'data' => [],
            ]),
        ];

        $userNotifications = $this->user->notifications;

        $this->assertCount(2, $userNotifications);
        $this->assertTrue($userNotifications->pluck('type')->contains('leave_approved'));
        $this->assertTrue($userNotifications->pluck('type')->contains('business_trip_approved'));
    }

    /** @test */
    public function notification_can_be_queried_by_type()
    {
        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'leave_approved',
            'data' => [],
        ]);

        Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'business_trip_approved',
            'data' => [],
        ]);

        $leaveNotifications = Notification::where('type', 'leave_approved')->get();

        $this->assertCount(1, $leaveNotifications);
        $this->assertEquals('leave_approved', $leaveNotifications->first()->type);
    }

    /** @test */
    public function notification_data_json_field_works_correctly()
    {
        $testData = [
            'leave_id' => '123',
            'reason' => 'Sakit',
            'duration_days' => 3,
        ];

        $notification = Notification::create([
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'type' => 'leave_approved',
            'data' => $testData,
        ]);

        // Verify JSON data is stored and retrieved correctly
        $this->assertEquals($testData, $notification->data);
        $this->assertEquals('Sakit', $notification->data['reason']);
    }
}
