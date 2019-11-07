<?php

namespace winwin\faker\providers;


class MiscTest extends \PHPUnit\Framework\TestCase
{

    public function testDistribute()
    {
        $misc = new Misc();
        $p = $misc->distribute(10);
        var_export($p);
    }
}
