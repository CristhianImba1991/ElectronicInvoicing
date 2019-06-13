<?php

namespace ElectronicInvoicing\StaticClasses;

class ObjectCast
{
    public function getObject($id, $name)
    {
        $object = new \stdClass;
        $object->id = $id;
        $object->name = $name;
        return $object;
    }
}
