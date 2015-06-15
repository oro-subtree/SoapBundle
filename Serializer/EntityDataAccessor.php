<?php

namespace Oro\Bundle\SoapBundle\Serializer;

use Doctrine\Common\Util\ClassUtils;

class EntityDataAccessor implements DataAccessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function hasGetter($className, $property)
    {
        $suffix = $this->camelize($property);

        if (method_exists($className, 'get' . $suffix)) {
            return true;
        }
        if (method_exists($className, 'is' . $suffix)) {
            return true;
        }
        if (method_exists($className, 'has' . $suffix)) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function tryGetValue($object, $property, &$value)
    {
        if (is_array($object)) {
            if (isset($object[$property]) || array_key_exists($property, $object)) {
                $value = $object[$property];

                return true;
            }
        } else {
            $suffix = $this->camelize($property);

            $accessor = 'get' . $suffix;
            if (method_exists($object, $accessor)) {
                $value = $object->$accessor();

                return true;
            }
            $accessor = 'is' . $suffix;
            if (method_exists($object, $accessor)) {
                $value = $object->$accessor();

                return true;
            }
            $accessor = 'has' . $suffix;
            if (method_exists($object, $accessor)) {
                $value = $object->$accessor();

                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($object, $property)
    {
        $value = null;
        if (!$this->tryGetValue($object, $property, $value)) {
            if (is_array($object)) {
                throw new \RuntimeException(
                    sprintf(
                        'Cannot get a value of "%s" field.',
                        $property
                    )
                );
            } else {
                throw new \RuntimeException(
                    sprintf(
                        'Cannot get a value of "%s" field from "%s" entity.',
                        $property,
                        ClassUtils::getClass($object)
                    )
                );
            }
        };

        return $value;
    }

    /**
     * Camelizes a given string.
     *
     * @param string $string Some string
     *
     * @return string The camelized version of the string
     */
    protected function camelize($string)
    {
        return strtr(ucwords(strtr($string, ['_' => ' '])), [' ' => '']);
    }
}
