<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace Cart\Test\TestCase\Controller\Component;

use Cake\Controller\ComponentRegistry;
use Cake\ORM\Entity;
use Cake\TestSuite\TestCase;
use Cart\Controller\Component\CartComponent;
use Cart\Entity\EntityBuyableAwareInterface;
use Cart\Entity\EntityBuyableLimitAwareInterface;
use Cart\Exception\BuyableLimitExceededException;

class Item extends Entity implements EntityBuyableAwareInterface
{
    protected $_accessible = [
        'price' => true,
    ];

    public $buyable_limit = INF;

    public function getPrice()
    {
        return $this->price;
    }

    public function getBuyableLimit()
    {
        return $this->buyable_limit;
    }
}

/**
 * @author Rafael Queiroz <rafaelfqf@gmail.com>
 */
class CartComponentTest extends TestCase
{

    /**
     * @var \Cart\Controller\Component\CartComponent
     */
    public $Cart;

    /**
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $controller = new \Cake\Controller\Controller();
        $registry = new ComponentRegistry($controller);
        $this->Cart = new CartComponent($registry);
    }

    /**
     * @return void
     */
    public function tearDown()
    {
        unset($this->Cart);
        parent::tearDown();
    }

    /**
     * @return void
     */
    public function testInitialization()
    {

    }

    /**
     * @return void
     */
    public function testAddInvalidQuantity()
    {
        $this->expectException(\Exception::class);
        $this->Cart->add(new Item(), 'two');
    }

    /**
     * @return void
     */
    public function testAddDuplicateItem()
    {
        $item = new Item();

        $this->assertTrue($this->Cart->add($item));
        $this->assertTrue($this->Cart->add($item));
    }

    /**
     * @return void
     */
    public function testAdd()
    {
        $this->assertTrue($this->Cart->add(new Item()));
    }

    /**
     * @return void
     */
    public function testAddBuyableLimitExeeded()
    {
        $this->expectException(BuyableLimitExceededException::class);

        $item = new Item();
        $item->buyable_limit = 1;

        $this->Cart->add($item, 2);
    }

    /**
     * @return void
     */
    public function testEditItemNotFound()
    {
        $this->expectException(\Exception::class);
        $this->Cart->edit(new Item());
    }

    /**
     * @return void
     */
    public function testEditBuyableLimitExceeded()
    {
        $this->expectException(BuyableLimitExceededException::class);

        $item = new Item();
        $item->buyable_limit = 1;

        $this->Cart->add($item);
        $this->assertEquals(1, $this->Cart->count());

        $this->Cart->edit($item, 2);
    }

    /**
     * @return void
     */
    public function testEdit()
    {
        $item = new Item();
        $this->Cart->add($item);
        $this->assertTrue($this->Cart->edit($item, 5));
    }

    /**
     * @return void
     */
    public function testDeleteItemNotFound()
    {
        $this->expectException(\Exception::class);
        $this->Cart->delete(new Item());
    }

    /**
     * @return void
     */
    public function testDelete()
    {
        $item = new Item();
        $this->Cart->add($item);
        $this->assertTrue($this->Cart->delete($item));
    }

    /**
     * @return void
     */
    public function testTotal()
    {
        $item = new Item();
        $item->price = 50;

        $this->Cart->add($item, 3);
        $this->assertTrue($this->Cart->total() === 150);
    }

    /**
     * @return void
     */
    public function testTotalEachItem()
    {
        $item1 = new Item();
        $item1->price = 50;

        $item2 = new Item();
        $item2->price = 70;

        $this->Cart->add($item1, 3);
        $this->Cart->add($item2, 2);

        $this->assertEquals(150, $this->Cart->total($item1));
        $this->assertEquals(140, $this->Cart->total($item2));
        $this->assertEquals(290, $this->Cart->total());
    }

    /**
     * @return void
     */
    public function testClear()
    {
        $item = new Item();
        $item->price = 50;

        $this->Cart->add($item);
        $this->Cart->clear();

        $this->assertTrue($this->Cart->get() === []);
        $this->assertEquals(0, $this->Cart->count());
        $this->assertEquals(0, $this->Cart->total());
    }

    /**
     * @return void
     */
    public function testCount()
    {
        $item = new Item();

        $this->assertEquals(0, $this->Cart->count());

        $this->Cart->add($item, 2);
        $this->assertEquals(2, $this->Cart->count());
    }

    /**
     * @return void
     */
    public function testCountEachItem()
    {
        $item1 = new Item();
        $item1->price = 50;

        $item2 = new Item();
        $item2->price = 70;

        $this->Cart->add($item1, 2);
        $this->Cart->add($item2, 3);

        $this->assertEquals(2, $this->Cart->count($item1));
        $this->assertEquals(3, $this->Cart->count($item2));
        $this->assertEquals(5, $this->Cart->count());
    }

    /**
     * @return void
     */
    public function testGet()
    {
        $item = new Item();

        $this->Cart->add($item);

        $this->assertEquals($item, $this->Cart->get($item)['entity']);
    }
}
