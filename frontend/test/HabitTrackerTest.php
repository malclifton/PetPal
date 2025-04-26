<?php
namespace Tests;

use PHPUnit\Framework\TestCase;

class HabitTrackerTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [
            'user_id' => 7,
            'user_name' => 'kevin pham',
            'role' => 'owner'
        ];
    }

    protected function tearDown(): void
    {
        session_destroy();
    }

    public function testAccessHabitTrackerPageSuccessfully()
    {
        $_GET = [
            'date' => date('Y-m-d'), // ✅ Send date for fetchHabits.php
            'testing' => 'true'
        ];

        ob_start();
        include __DIR__ . '/../../php/fetchHabits.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('habit_id', $output);
    }

    public function testDisplayExistingHabitData()
    {
        $_GET = [
            'pet_id' => 6, // ✅ Send pet_id for fetchHabits.php
            'testing' => 'true'
        ];

        ob_start();
        include __DIR__ . '/../../php/fetchHabits.php';
        $output = ob_get_clean();

        $this->assertStringContainsString('habit_type', $output);
    }

    public function testUpdateExistingHabitData()
    {
        $_POST = [
            'habit_id' => 4, // ✅ your correct habit_id
            'habit_type' => 'feeding',
            'habit_description' => 'Updated feeding habit',
            'habit_time' => '2025-05-01T10:00'
        ];
        $_GET = ['testing' => 'true'];

        ob_start();
        include __DIR__ . '/../../php/updateHabit.php';
        $output = ob_get_clean();

        $this->assertEquals('success', trim($output));
    }

    public function testHabitFormSubmissionWithIncompleteData()
    {
        $_POST = [
            'pet_id' => '',
            'habit_type' => '',
            'habit_description' => '',
            'habit_time' => ''
        ];
        $_GET = ['testing' => 'true'];

        ob_start();
        include __DIR__ . '/../../php/addHabit.php';
        $output = ob_get_clean();

        $this->assertEquals('All fields must be completed', trim($output));
    }
}
?>
