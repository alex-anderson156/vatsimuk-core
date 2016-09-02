<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class MshipStateTest extends TestCase
{
    use DatabaseTransactions;

    private $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = factory(App\Models\Mship\Account::class)->create([
            "id" => 123456,
            "name_first" => "John",
            "name_last" => "Doe",
            "email" => "i_sleep@gmail.com",
        ]);
    }

    /** @test */
    public function it_adds_a_state()
    {
        $divisionState = \App\Models\Mship\State::findByCode("DIVISION");

        $this->account->addState($divisionState);

        $this->seeInDatabase("mship_account_state", [
            "account_id" => $this->account->id,
            "state_id" => $divisionState->id,
            "end_at" => null,
        ]);
    }

    /** @test */
    public function it_deletes_old_permanent_state()
    {
        $divisionState = \App\Models\Mship\State::findByCode("DIVISION");
        $regionState = \App\Models\Mship\State::findByCode("REGION");

        $this->account->addState($regionState);

        $this->seeInDatabase("mship_account_state", [
            "account_id" => $this->account->id,
            "state_id" => $regionState->id,
            "end_at" => null,
        ]);

        $this->account->fresh()->addState($divisionState);

        $this->seeInDatabase("mship_account_state", [
            "account_id" => $this->account->id,
            "state_id" => $divisionState->id,
            "end_at" => null,
        ]);

        $this->notSeeInDatabase("mship_account_state", [
            "account_id" => $this->account->id,
            "state_id" => $regionState->id,
            "end_at" => null,
        ]);

        $this->seeInDatabase("mship_account_state", [
            "account_id" => $this->account->id,
            "state_id" => $regionState->id
        ]);
    }

    /** @test */
    public function it_keeps_current_temporary_state()
    {
        $visitorState = \App\Models\Mship\State::findByCode("VISITING");
        $regionState = \App\Models\Mship\State::findByCode("REGION");

        $this->account->addState($visitorState);

        $this->seeInDatabase("mship_account_state", [
            "account_id" => $this->account->id,
            "state_id" => $visitorState->id,
            "end_at" => null,
        ]);

        $this->account->fresh()->addState($regionState);

        $this->seeInDatabase("mship_account_state", [
            "account_id" => $this->account->id,
            "state_id" => $visitorState->id,
            "end_at" => null,
        ]);

        $this->seeInDatabase("mship_account_state", [
            "account_id" => $this->account->id,
            "state_id" => $visitorState->id,
            "end_at" => null,
        ]);
    }

    /** @test */
    public function it_deletes_temporary_states_when_delete_all_temps_state_is_added()
    {
        $visitorState = \App\Models\Mship\State::findByCode("VISITING");
        $divisionState = \App\Models\Mship\State::findByCode("DIVISION");

        $this->account->addState($visitorState);

        $this->seeInDatabase("mship_account_state", [
            "account_id" => $this->account->id,
            "state_id" => $visitorState->id,
            "end_at" => null,
        ]);

        $this->account->fresh()->addState($divisionState);

        $this->seeInDatabase("mship_account_state", [
            "account_id" => $this->account->id,
            "state_id" => $divisionState->id,
            "end_at" => null,
        ]);

        $this->notSeeInDatabase("mship_account_state", [
            "account_id" => $this->account->id,
            "state_id" => $visitorState->id,
            "end_at" => null,
        ]);

        $this->seeInDatabase("mship_account_state", [
            "account_id" => $this->account->id,
            "state_id" => $visitorState->id,
        ]);
    }

    /** @test */
    public function it_returns_correct_primary_state_when_only_one_exists()
    {
        $divisionState = \App\Models\Mship\State::findByCode("DIVISION");

        $this->account->addState($divisionState);

        $this->assertEquals($divisionState->id, $this->account->fresh()->primary_state->id);
    }

    /** @test */
    public function it_returns_correct_primary_state_when_temporary_overrides()
    {
        $regionState = \App\Models\Mship\State::findByCode("REGION");
        $visitorState = \App\Models\Mship\State::findByCode("VISITING");

        $this->account->addState($regionState);
        $this->account->addState($visitorState);

        $this->assertEquals($visitorState->id, $this->account->fresh()->primary_state->id);
    }

    /** @test */
    public function it_returns_correct_primary_state_when_temporary_overrides_and_multiple_temporary()
    {
        $regionState = \App\Models\Mship\State::findByCode("REGION");
        $visitorState = \App\Models\Mship\State::findByCode("VISITING");
        $transferringState = \App\Models\Mship\State::findByCode("TRANSFERRING");

        $this->account->addState($regionState);
        $this->account->addState($visitorState);
        $this->account->addState($transferringState);

        $this->assertEquals($transferringState->id, $this->account->fresh()->primary_state->id);
    }
}