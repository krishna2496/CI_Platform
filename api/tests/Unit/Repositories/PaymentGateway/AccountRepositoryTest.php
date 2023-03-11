<?php

namespace Tests\Unit\Repositories\PaymentGateway;

use App\Models\PaymentGateway\PaymentGatewayAccount;
use App\Repositories\PaymentGateway\AccountRepository;
use Mockery;
use Illuminate\Pagination\LengthAwarePaginator;
use TestCase;

class AccountRepositoryTest extends TestCase
{
    /**
     * Test getByOrgId method
     *
     * @return void
     */
    public function testGetByOrgId()
    {
        $orgId = 'organizationID';

        $paymentGatewayAccount = new PaymentGatewayAccount();
        $paymentGatewayAccount
            ->setAttribute('organization_id', $orgId)
            ->setAttribute('payment_gateway_account_id', 'payment_gateway_account_id')
            ->setAttribute('payment_gateway', 'payment_gateway');

        $model = $this->mock(PaymentGatewayAccount::class);
        $model->shouldReceive('where')
            ->once()
            ->with('organization_id', '=', $orgId)
            ->andReturnSelf()
            ->shouldReceive('first')
            ->once()
            ->andReturn($paymentGatewayAccount);

        $response = $this->getRepository($model)->getByOrgId($orgId);

        $this->assertSame($paymentGatewayAccount, $response);
    }

    /**
     * Test Save method
     *
     * @return void
     */
    public function testSave()
    {
        $paymentGatewayAccount = new PaymentGatewayAccount();
        $paymentGatewayAccount
            ->setAttribute('organization_id', 'orgID')
            ->setAttribute('payment_gateway_account_id', 'payment_gateway_account_id')
            ->setAttribute('payment_gateway', 1);
        $paymentGatewayAccountType = 1;

        $model = $this->mock(PaymentGatewayAccount::class);
        $model
            ->shouldReceive('updateOrCreate')
            ->once()
            ->with([
                'organization_id' => $paymentGatewayAccount->organization_id,
                'payment_gateway_account_id' => $paymentGatewayAccount->payment_gateway_account_id
            ],[
                'payment_gateway' => $paymentGatewayAccountType
            ])
            ->andReturn($paymentGatewayAccount)
            ->shouldReceive('where')
            ->times(3)
            ->andReturnSelf()
            ->shouldReceive('restore')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('delete')
            ->once()
            ->andReturnSelf();

        $response = $this->getRepository($model)->save($paymentGatewayAccount);

        $this->assertSame($paymentGatewayAccount, $response);
    }

    /**
     * Test Delete method
     *
     * @return void
     */
    public function testDelete()
    {
        $filters = [
            'organization_id' => 'organizationID'
        ];

        $model = $this->mock(PaymentGatewayAccount::class);
        $model->shouldReceive('where')
            ->once()
            ->with($filters)
            ->andReturnSelf()
            ->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        $response = $this->getRepository($model)->delete($filters);

        $this->assertTrue($response);
    }

    /**
     * Create a new repository instance.
     *
     * @param PaymentGatewayAccount $model
     *
     * @return void
     */
    private function getRepository(PaymentGatewayAccount $model)
    {
        return new AccountRepository($model);
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
