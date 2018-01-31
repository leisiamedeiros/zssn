<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Survivor;

class SurvivorTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @group RTE01
     * @group RTE01CN001
     * @testdox CN001 Create survivor data pass
     */
    public function testCreateSurvivor()
    {
        $response = $this->json('POST', '/api/survivor/new', [
          'name' => 'Sally',
          'age' => 18,
          'gender' => 'female',
          'lat' => '-1023223',
          'long' => '1230299',
          'items' => '1:4, 2:5'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                    'status' => 'OK',
                 ]);

        $this->assertDatabaseHas('survivors', [
            'name' => 'Sally'
        ]);
    }

    /**
     * @group RTE01
     * @group RTE01CN002
     * @testdox CN002 Create survivor data fail
     */
    public function testCreateSurvivorFail()
    {
        $response = $this->json('POST', '/api/survivor/new', [
          'name' => '',
          'age' => '18',
          'gender' => '',
          'lat' => '-1023223',
          'long' => '1230299',
          'items' => '11'
        ]);

        $response->assertStatus(422)
                 ->assertJson([
                   "name" => [
                       "The name field is required."
                   ],
                   "gender" => [
                       "The gender field is required."
                   ],
                   "items" => [
                        "The items needs be in the format id:quantity, id:quantity"
                   ]
                 ]);

        $this->assertDatabaseMissing('survivors', [
            'name' => 'Sally'
        ]);
    }

    /**
     * @group RTE01
     * @group RTE01CN003
     * @testdox CN003 Update survivor location
     */
    public function testUpdateLocation()
    {
        $this->json('POST', '/api/survivor/new', [
          'name' => 'Sally',
          'age' => 18,
          'gender' => 'female',
          'lat' => '-888888',
          'long' => '888888',
          'items' => '1:4, 2:5'
        ]);

        $this->assertDatabaseHas('survivors', [
            'name' => 'Sally',
            'lat' => '-888888',
            'long' => '888888'
        ]);

        $user = Survivor::whereName('Sally')->first();

        $response = $this->json('PUT', '/api/survivor/'.$user->id.'/location/update', [
            'lat' => '-1023223',
            'long' => '1230299'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                    'message' => true,
                 ]);

    }

}
