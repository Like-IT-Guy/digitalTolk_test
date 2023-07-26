// tests/Unit/BookingControllerTest.php
<?php


use Tests\TestCase;
use Carbon\Carbon;
use DTApi\Http\Controllers\BookingController;
use DTApi\Models\User;
use DTApi\Models\UserMeta;
use DTApi\Models\UsersBlacklist;
use DTApi\Models\Type;
use DTApi\Models\Company;
use DTApi\Models\Department;
use DTApi\Models\Town;
use DTApi\Models\UserTowns;
use DTApi\Models\UserLanguages;
use Mockery;
use Illuminate\Support\Facades\DB;

class BookingControllerTest extends TestCase
{
    // Your other tests go here...

    public function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }

    public function testWillExpireAt()
    {
        // Create mock date values for testing
        $due_time = '2023-07-26 15:00:00';
        $created_at = '2023-07-25 12:30:00';

        // Call the willExpireAt method
        $result = BookingController::willExpireAt($due_time, $created_at);

        // Calculate the expected result
        $due_time_parsed = Carbon::parse($due_time);
        $created_at_parsed = Carbon::parse($created_at);
        $difference = $due_time_parsed->diffInHours($created_at_parsed);

        if ($difference <= 90) {
            $expected_result = $due_time_parsed;
        } elseif ($difference <= 24) {
            $expected_result = $created_at_parsed->addMinutes(90);
        } elseif ($difference > 24 && $difference <= 72) {
            $expected_result = $created_at_parsed->addHours(16);
        } else {
            $expected_result = $due_time_parsed->subHours(48);
        }

        // Assert that the result matches the expected result
        $this->assertEquals($expected_result->format('Y-m-d H:i:s'), $result);
    }

    // tests/Unit/BookingControllerTest.php

    public function testCreateOrUpdate()
    {
        // Mock data for testing
        $id = 1;
        $request = [
            'role' => 'translator',
            'name' => 'John Doe',
            // Add other required data here...
        ];

        // Create mock objects for User and UserMeta
        $user = Mockery::mock(User::class);
        $userMeta = Mockery::mock(UserMeta::class);
        $userMetaClass = Mockery::mock(UserMeta::class);
        $type = Mockery::mock(Type::class);
        $company = Mockery::mock(Company::class);
        $department = Mockery::mock(Department::class);
        $town = Mockery::mock(Town::class);
        $userTowns = Mockery::mock(UserTowns::class);
        $userLanguages = Mockery::mock(UserLanguages::class);
        $usersBlacklist = Mockery::mock(UsersBlacklist::class);

        // Expectations for the User model and UserMeta
        User::shouldReceive('findOrFail')
            ->with($id)
            ->andReturn($user);

        User::shouldReceive('save')
            ->once();

        UserMeta::shouldReceive('firstOrCreate')
            ->with(['user_id' => $id])
            ->andReturn($userMeta);

        $userMeta->shouldReceive('save')
            ->once();

        // Add other expectations for related classes here...

        // Create an instance of BookingController
        $controller = new BookingController();

        // Call the createOrUpdate method
        $result = $controller->createOrUpdate($id, $request);

        // Assert the result
        $this->assertSame($user, $result);
    }

}
