<?php

namespace Tests\Feature;

use Tests\TestCase;
use FunkyDuck\Querychan\Models\Users;

class UserTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Users::migrate();
    }

    /**
     * Insert and Find
     * @test 
     */
    public function test_it_can_create_and_find_a_user(): void
    {
        $user = new Users([
            'name' => 'Ginji',
            'email' => 'ginji@funkyduck.be',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'role' => 'admin'
        ]);

        $user->save();
        
        $this->assertNotNull($user->id, "User need an ID after saving");
        $this->assertIsInt($user->id, "User ID requiert to be an INT");
        
        $foundUser = Users::find($user->id);
        
        $this->assertNotNull($foundUser, "User need to be found in DB");
        
        $this->assertEquals('Ginji', $foundUser->name, "Username is incorrect");
        $this->assertEquals('ginji@funkyduck.be', $foundUser->email, "User email is incorrect");
    }
    
    /**
     * Delete
     * @test
    */
    public function test_it_can_delete_a_user(): void {
        $user = new Users([
            'name' => 'Frank',
            'email' => 'Franky@usermail.fr',
            'password' => password_hash('password', PASSWORD_DEFAULT),
        ]);
        
        $user->save();
        $userId = $user->id;
        $this->assertNotNull(Users::find($userId), "User need to be found in DB before delete");

        $result = $user->delete();

        $this->assertTrue($result, "delete() Method need to return true is case of success");
        $this->assertNull(Users::find($userId), "User can't be found in DB");
    }
}