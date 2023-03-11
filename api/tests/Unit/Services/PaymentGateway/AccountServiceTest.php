<?php

namespace Tests\Unit\Services\PaymentGateway;

use App\Models\PaymentGateway\PaymentGatewayAccount;
use App\Repositories\PaymentGateway\AccountRepository;
use App\Services\PaymentGateway\AccountService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use TestCase;

class AccountServiceTest extends TestCase
{
    /**
     * Test getByOrgId
     *
     * @return void
     */
    public function testGetByOrgId()
    {
        $organizationId = 'orgID';

        $paymentGatewayAccount = new PaymentGatewayAccount();
        $paymentGatewayAccount
            ->setAttribute('organization_id', 'orgID')
            ->setAttribute('payment_gateway_account_id', 'payment_gateway_account_id')
            ->setAttribute('payment_gateway', 'payment_gateway');

        $accountRepository = $this->mock(AccountRepository::class);
        $accountRepository->shouldReceive('getByOrgId')
            ->once()
            ->with($organizationId)
            ->andReturn($paymentGatewayAccount);

        $response = $this->getService(
            $accountRepository
        )->getByOrgId($organizationId);

        $this->assertSame($paymentGatewayAccount, $response);
    }

    /**
     * Test save
     *
     * @return void
     */
    public function testSave()
    {
        $paymentGatewayAccount = new PaymentGatewayAccount();
        $paymentGatewayAccount
            ->setAttribute('organization_id', 'orgID')
            ->setAttribute('payment_gateway_account_id', 'payment_gateway_account_id')
            ->setAttribute('payment_gateway', 'payment_gateway');

        $accountRepository = $this->mock(AccountRepository::class);
        $accountRepository->shouldReceive('save')
            ->once()
            ->with($paymentGatewayAccount)
            ->andReturn($paymentGatewayAccount);

        $response = $this->getService(
            $accountRepository
        )->save($paymentGatewayAccount);

        $this->assertSame($paymentGatewayAccount, $response);
    }

    /**
     * Test delete
     *
     * @return void
     */
    public function testDelete()
    {
        $filters = [
            'organization_id' => 'organizationID'
        ];
        $accountRepository = $this->mock(AccountRepository::class);
        $accountRepository->shouldReceive('delete')
            ->once()
            ->with($filters)
            ->andReturn(true);

        $response = $this->getService(
            $accountRepository
        )->delete($filters);

        $this->assertTrue($response);
    }

    /**
     * Create a new service instance.
     *
     * @param  AccountRepository $accountRepository
     *
     * @return void
     */
    private function getService(AccountRepository $accountRepository)
    {
        return new AccountService(
            $accountRepository
        );
    }

    /**
    * Mock an object
    *
    * @param string name
    *
    * @return Mockery
    */
    private function mock($class)
    {
        return Mockery::mock($class);
    }
}
