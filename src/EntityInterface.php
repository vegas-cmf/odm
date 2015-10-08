<?php
/**
 * @author Slawomir Zytko <slawek@amsterdam-standard.pl>
 * @company Amsterdam Standard Sp. z o.o.
 */


namespace Vegas\ODM;


interface EntityInterface
{
    public function getSource();

    public function getRepository();
}