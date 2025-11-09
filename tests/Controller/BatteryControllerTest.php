<?php

namespace App\Tests\Controller;

use App\Entity\Battery;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class BatteryControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $batteryRepository;
    private string $path = '/battery/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->batteryRepository = $this->manager->getRepository(Battery::class);

        foreach ($this->batteryRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Battery index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'battery[name]' => 'Testing',
            'battery[user.id]' => 'Testing',
            'battery[createdAt]' => 'Testing',
            'battery[batteryBankId]' => 'Testing',
            'battery[updatedAt]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->batteryRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Battery();
        $fixture->setName('My Title');
        $fixture->setUserId('My Title');
        $fixture->setCreatedAt('My Title');
        $fixture->setBatteryBankId('My Title');
        $fixture->setUpdatedAt('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Battery');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Battery();
        $fixture->setName('Value');
        $fixture->setUserId('Value');
        $fixture->setCreatedAt('Value');
        $fixture->setBatteryBankId('Value');
        $fixture->setUpdatedAt('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'battery[name]' => 'Something New',
            'battery[userId]' => 'Something New',
            'battery[createdAt]' => 'Something New',
            'battery[batteryBankId]' => 'Something New',
            'battery[updatedAt]' => 'Something New',
        ]);

        self::assertResponseRedirects('/battery/');

        $fixture = $this->batteryRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getUserId());
        self::assertSame('Something New', $fixture[0]->getCreatedAt());
        self::assertSame('Something New', $fixture[0]->getBatteryBankId());
        self::assertSame('Something New', $fixture[0]->getUpdatedAt());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Battery();
        $fixture->setName('Value');
        $fixture->setUserId('Value');
        $fixture->setCreatedAt('Value');
        $fixture->setBatteryBankId('Value');
        $fixture->setUpdatedAt('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/battery/');
        self::assertSame(0, $this->batteryRepository->count([]));
    }
}
