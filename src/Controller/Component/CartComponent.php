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

namespace Cart\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;

/**
 * @author Rafael Queiroz <rafaelfqf@gmail.com>
 */
class CartComponent extends Component
{

    /**
     * @var array
     */
    protected $_defaultConfig = [
        'storage' => \Cart\Storage\SessionStorage::class
    ];

    /**
     * @var array
     */
    protected $_objects = [];

    /**
     * @var \Cart\Storage\StorageInterface
     */
    protected $_storage;

    /**
     * @param array $config
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->storage(new $this->_config['storage']($this->_registry->getController()->request));
        $this->_objects = $this->storage()->read();
    }

    /**
     * @param \Cart\Entity\EntityPriceAwareInterface $entity
     * @param int $quantity
     * @return bool
     * @throws \Exception
     */
    public function add(\Cart\Entity\EntityPriceAwareInterface $entity, $quantity = 1)
    {
        $this->_validate($entity, $quantity);
        if ($this->_entityExists($entity)) {
            return $this->edit($entity, $this->count($entity) + $quantity);
        }

        $this->_objects[] = [
            'entity' => $entity,
            'quantity' => $quantity,
            'total' => $entity->getPrice() * $quantity,
        ];

        $this->storage()->write($this->_objects);
        return true;
    }

    /**
     * @param \Cart\Entity\EntityPriceAwareInterface $entity
     * @param int $quantity
     * @return bool
     * @throws \Exception
     */
    public function edit(\Cart\Entity\EntityPriceAwareInterface $entity, $quantity = 1)
    {
        $this->_validate($entity, $quantity);
        foreach ($this->_objects as &$object) {
            if ($object['entity'] == $entity) {
                $object['quantity'] = $quantity;
                $object['total'] = $entity->getPrice() * $object['quantity'];
                $this->storage()->write($this->_objects);

                return true;
            }
        }

        throw new \Exception();
    }

    /**
     * @param \Cart\Entity\EntityPriceAwareInterface $entity
     * @return bool
     * @throws \Exception
     */
    public function delete(\Cart\Entity\EntityPriceAwareInterface $entity)
    {
        foreach ($this->_objects as $key => $object) {
            if ($object['entity'] == $entity) {
                unset ($this->_objects[$key]);
                $this->storage()->write($this->_objects);
                return true;
            }
        }


        throw new \Exception();
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->storage()->read();
    }

    /**
     * @param \Cart\Entity\EntityPriceAwareInterface|null $entity
     * @return mixed
     * @throws \Exception
     */
    public function count(\Cart\Entity\EntityPriceAwareInterface $entity = null)
    {
        if ($entity) {
            foreach ($this->_objects as $object) {
                if ($object['entity'] == $entity) {
                    return $object['quantity'];
                }
            }
            throw new \Exception();
        }

        return array_reduce($this->get(), function ($count, $object) {
            return $count + $object['quantity'];
        }, 0);
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->storage()->delete();
    }


    /**
     * @return int
     */
    public function total(\Cart\Entity\EntityPriceAwareInterface $entity = null)
    {
        $total = 0;

        if ($entity) {
            foreach ($this->_objects as $object) {
                if ($object['entity'] == $entity) {
                    return $object['total'];
                }
            }
            throw new \Exception();
        }

        foreach ($this->_objects as $object) {
            $total += $object['total'];
        }

        return $total;
    }

    /**
     * @param \Cart\Storage\StorageInterface $storage
     * @return \Cart\Storage\StorageInterface
     */
    public function storage(\Cart\Storage\StorageInterface $storage = null)
    {
        if (!$this->_storage instanceof \Cart\Storage\StorageInterface) {
            $this->_storage = $storage;
        }

        return $this->_storage;
    }

    /**
     * @param $entity
     * @param $quantity
     * @throws \Exception
     */
    protected function _validate($entity, $quantity)
    {
        if (!$entity instanceof \Cart\Entity\EntityPriceAwareInterface) {
            throw new \Exception();
        }
        if ($quantity < 1) {
            throw new \Exception();
        }
    }

    /**
     * @param $entity
     * @return bool
     */
    protected function _entityExists($entity)
    {
        foreach ($this->_objects as $object) {
            if ($object['entity'] == $entity) {
                return true;
            }
        }

        return false;
    }

}
