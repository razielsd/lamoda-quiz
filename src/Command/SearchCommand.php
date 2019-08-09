<?php

namespace App\Command;

use App\ApiClient\Client;
use App\Search\Container;
use App\Search\ContainerList;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchCommand extends Command
{
    /**
     * Максимальное число контейнеров, которые можно использовать
     */
    const  MAX_CONTAINERS = 1000;

    /**
     * Кол-во контейнеров которые за один раз загружаются
     */
    const CHUNK_SIZE = 100;

    /**
     * Максимальное кол-во контейнеров не подходящих контейнеров для ослабления условий подбора контейнеров
     */
    const MAX_BAD_LEN = 1000;

    /**
     * Очередь обработки
     *
     * @var array
     */
    protected $processedIndex = [];
    /**
     * Уровень жадности
     *
     * @var int
     */
    protected $greedyIndex = 0;
    /**
     * Всего загружено контейнеров
     *
     * @var int
     */
    protected $containerCounter = 0;
    /**
     * Счетчик контейнеров, которые подряд не подошли
     * @var int
     */
    protected $badCounter = 0;
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setProcessTitle('lamoda');
        $this->setName('lamoda-search');
        $this->setDescription('Search minimal containers with all items');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->client = new Client();
        $containerItemCount = 10;
        $uniqItem = 100;
        for ($i=1;$i<$containerItemCount;$i++) {
            $this->processedIndex[$i] = [];
        }
        $this->greedyIndex = 0;


        $containerList = new ContainerList();
        $output->writeln("Start search ....");
        while (true) {
            $found = $this->loadContainer($this->greedyIndex);
            //Если больше нет доступных контейнеров - делаем поиск менее жадным
            if ($found === 0) {
                $output->writeln("All containers loaded, greedyIndex++");
                $this->increaseGreedy();
            }
            //Если долго не можем найти подходящий контейнер - делаем поиск менее жадным
            if ($this->badCounter >= self::MAX_BAD_LEN) {
                $this->increaseGreedy();
                $output->writeln("Cannot fit containers, greedyIndex++");
            }
            if (count($this->processedIndex[$this->greedyIndex]) == 0) {
                $this->increaseGreedy();
                $output->writeln("Current index processed, greedyIndex++");
            }
            //дальше снижать жадность некуда, выходим
            if ($this->greedyIndex >= ($containerItemCount - 1)) {
                $output->writeln('Cannot combine all items');
                break;
            }
            $this->fillBagback($containerList);
            //добавлены контейнеры с товарами каждого типа
            if ($containerList->getUniqIdCount() == $uniqItem) {
                break;
            }
        }
        $output->writeln('Items: ' . $containerList->getUniqIdCount());
        $output->writeln('Containers: ' . $containerList->count());
        //проверяем, что все товары действительно нашли
        $map = $containerList->getUniqIdMap();
        for ($i=1;$i<=100;$i++) {
            if (!isset($map[$i])) {
                $output->writeln("Not found: {$i}");
            }
        }
        /** @var Container $container */
        foreach ($containerList as $container) {
            $output->writeln(
                'ContainerID: ' . $container->getId() . ', items: ' . join(', ', $container->getItemIdList())
            );
        }

    }


    /**
     * Основная часть алгоритма заполнения здесь:
     * сначала добавляем контейнеры, в которых больше всего подходит товаров, потом меньше
     *
     * @param ContainerList $containerList
     */
    protected function fillBagback(ContainerList $containerList)
    {
        /** @var Container $container */
        foreach ($this->processedIndex[$this->greedyIndex] as $container) {
            // узнаем сколько уникальных товаров таких уже есть в собранных контейнерах
            $intersect = $containerList->getIntersection($container);
            if ($this->greedyIndex >= count($intersect)) {
                $containerList->add($container);
                $this->badCounter = 0;
            } else {
                // сохраняем текущее кол-во совпадающих товаров, чтобы на следующих итерациях
                // не проверять заведомо не подходящие
                $this->processedIndex[count($intersect)][] = $container;
                ++$this->badCounter;
            }
        }
        $this->processedIndex[$this->greedyIndex] = [];
    }


    protected function loadContainer(): int
    {
        if ($this->containerCounter >= self::MAX_CONTAINERS) {
            return 0;
        }
        $containerList = $this->client->getContainer(self::CHUNK_SIZE, $this->containerCounter);
        $found = count($containerList);
        if ($found > 0) {
            $this->addToProcess($containerList);
            $this->containerCounter += $found;
        }
        return $found;
    }


    protected function addToProcess(array $containerList): self
    {
        foreach ($containerList as $container) {
            $this->processedIndex[$this->greedyIndex][] = new Container($container);
        }
        return $this;
    }


    protected function increaseGreedy(): int
    {
        ++$this->greedyIndex;
        $this->badCounter = 0;
        $this->output->writeln('greedyIndex: ' . $this->greedyIndex);
        return $this->greedyIndex;
    }

}
