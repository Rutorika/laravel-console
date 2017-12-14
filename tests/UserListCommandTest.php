<?php

require_once 'ConsoleTestBase.php';

class UserListModelTest extends ConsoleTestBase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testOK()
    {
        $this->assertEquals(1, 1);
    }
}
