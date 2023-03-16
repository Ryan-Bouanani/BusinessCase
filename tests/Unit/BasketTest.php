<?php

namespace App\Tests\Unit;

use App\Entity\Basket;
use App\Entity\Customer;
use App\Entity\MeanOfPayment;
use App\Enum\StatusEnum;
use App\Repository\StatusRepository;
use App\Tests\BaseTestCase;
use DateTime;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Component\Validator\ConstraintViolation;

class BasketTest extends BaseTestCase
{
    /** @var AbstractDatabaseTool */
    protected $databaseTool;



    /**
     * This controller will be used for get entity Basket
     *
     * @return Basket
     */
    public function createValidBasket(): Basket {
        $container = static::getContainer();
        $customer = $container->get('doctrine.orm.entity_manager')->find(Customer::class, 1);
        $meanOfPayment = $container->get('doctrine.orm.entity_manager')->find(MeanOfPayment::class, 1);

        $status = $container->get(StatusRepository::class)->findOneBy(['name' => StatusEnum::ACCEPTER]);

        $basket = (new Basket)->setBillingDate(new DateTime())
            ->setCustomer($customer)
            ->setAddress($customer->getAddress())
            ->setStatus($status)
            ->setMeanOfPayment($meanOfPayment)
        ;

        return $basket;
    }


    /**
     * This controller will be used for test the creation of order is valid
     *
     * @return void
     */
    public function testEntityIsValid(): void
    {
        self::bootKernel();

        $basket = $this->createValidBasket();
        $this->assertValidationErrorsCount($basket, 0);
    }












    public function setUp(): void
    {
        parent::setUp();
        $this->databaseTool = static::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    // public function testFixtureLoad():void {
    //     self::bootKernel();
    //     $this->databaseTool->loadFixtures([
    //             __DIR__ . '/fixtures/Address.yaml'
    //     ]);
    // }
}

