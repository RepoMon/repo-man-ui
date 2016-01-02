<?php namespace Test\Unit;

use Ace\RepoManUi\Remote\Repository;
use PHPUnit_Framework_TestCase;

/**
 * @author timrodger
 * Date: 02/01/16
 */
class RepositoryTest extends PHPUnit_Framework_TestCase
{

    public function getDependencyManagers()
    {
        return [
            ['PHP', 'composer'],
            ['php', 'composer'],
            ['javascript', 'npm'],
            ['JavaScript', 'npm'],
            ['Ruby', ''],
            ['', ''],
            ['C++', '']
        ];
    }

    /**
     * @dataProvider getDependencyManagers
     * @param $language
     * @param $manager
     */
    public function testGetDependencyManager($language, $manager)
    {

        $repository = new Repository('https://github.com/owner/repo', 'A repository', $language);

        $this->assertSame($manager, $repository->getDependencyManager());
    }


    public function testRepositoryIsInactiveByDefault()
    {

        $repository = new Repository('https://github.com/owner/repo', 'A repository', 'JavaScript');

        $this->assertFalse($repository->isActive());

        $repository->setActive(true);

        $this->assertTrue($repository->isActive());
    }

    public function testTimezoneIsEmptyByDefault()
    {

        $repository = new Repository('https://github.com/owner/repo', 'A repository', 'JavaScript');

        $this->assertSame('', $repository->getTimezone());

        $repository->setTimezone('Europe/London');

        $this->assertSame('Europe/London', $repository->getTimezone());
    }

    public function testGetFullName()
    {
        $repository = new Repository('https://github.com/owner/repo', 'A repository', 'JavaScript');

        $this->assertSame('owner/repo', $repository->getFullName());
    }

    public function testGetOwner()
    {
        $repository = new Repository('https://github.com/timr/repo', 'A repository', 'JavaScript');

        $this->assertSame('timr', $repository->getOwner());
    }

    public function testGetName()
    {
        $repository = new Repository('https://github.com/timr/repo', 'A repository', 'JavaScript');

        $this->assertSame('repo', $repository->getName());
    }
}
