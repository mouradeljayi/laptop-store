<?php

namespace App\Tests\Unit;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProductTest extends KernelTestCase
{

    public function getEntity(): Product 
    {
        # bring the category with id == 2 from database
        $category = static::getContainer()
                    ->get('doctrine.orm.entity_manager')->find(Category::class, 1);

        return (new Product())
            ->setName("Product #1")
            ->setDescription("Description #1")
            ->setPrice(3889)
            ->setQuantity(444)
            ->setCategory($category)
            ->setImage("image #1");

    }

    public function assertHasErrors(Product $product, int $number = 0)
    {
        self::bootKernel();
        $container = static::getContainer();
        $errors = $container->get('validator')->validate($product);
        $this->assertCount($number, $errors);
    }

    
    public function testEntityIsValid(): void
    {
        $this->assertHasErrors($this->getEntity(), 0);   
    }
}
