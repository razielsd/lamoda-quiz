<?php

declare(strict_types=1);

namespace App\Command;

use App\Storage\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FillCommand extends Command
{
    /**
     * @var array
     */
    protected $ids = [];
    protected $map = [];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setProcessTitle('lamoda');
        $this->setName('lamoda-db-fill');
        $this->setDescription('Fill database with containers');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     * @throws \App\Storage\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->map = array_fill(1, 100, 0);
        $totalContainer = 1000;
        $containerItemCount = 10;
        $uniqItem = 100;
        for ($i = 0;$i < $totalContainer; $i++) {
            $container = $this->createContainer($uniqItem, $containerItemCount);
            (new Storage())->storeContainer($container);
        }
        $output->writeln("Added {$totalContainer} containers with {$containerItemCount}, uniq items: {$uniqItem}");
        $check = array_filter($this->map, function ($v) {return $v == 0;});
        if (count($check) == 0) {
            $output->writeln("All item Id writen");
        } else {
            $output->writeln("Item Id not writen: " . join(', ', array_keys($check)));
        }
    }

    /**
     * @param int $uniqItem
     * @param int $itemCount
     * @return array
     */
    protected function createContainer(int $uniqItem, int $itemCount): array
    {
        $this->ids = range(1, $uniqItem);
        shuffle($this->ids);
        $items = [];
        for ($i = 0;$i < $itemCount;$i++) {
            $id = $this->ids[$i];
            $items[] = [
                'id' => $id,
                //да, названия для одинаковых id не совпадают
                'title' => $this->getDictString($id)
            ];
            $this->map[$id]++;
        }
        return ['title' => $this->getDictString(), 'items' => $items];
    }

    /**
     * @param null $idx
     * @return string
     */
    protected function getDictString($idx = null): string
    {
        $dict = '5.56 Rifle Ammo
            8x Zoom Scope
            12 Gauge Buckshot
            12 Gauge Incendiary Shell
            12 Gauge Slug
            16x Zoom Scope
            40mm HE Grenade
            40mm Shotgun Round
            40mm Smoke Grenade
            A Barrel Costume
            Acoustic Guitar
            AND Switch
            Animal Fat
            Anti-Radiation Pills
            Apple
            Armored Door
            Armored Double Door
            Assault Rifle
            Audio Alarm
            Auto Turret
            Bandage
            Bandana Mask
            Barbed Wooden Barricade
            Barbeque
            Baseball Cap
            Battery - Small
            Beancan Grenade
            Bed
            Beenie Hat
            Binoculars
            Birthday Cake
            Black Raspberries
            Bleach
            Blocker
            Blood
            Blue Keycard
            Blueberries
            Blueprint
            Bolt Action Rifle
            Bone Armor
            Bone Arrow
            Bone Club
            Bone Fragments
            Bone Helmet
            Bone Knife
            Boonie Hat
            Boots
            Bota Bag
            Bronze Egg
            Bucket Helmet
            Building Plan
            Bunny Ears
            Bunny Onesie
            Burlap Gloves
            Burlap Headwrap
            Burlap Shirt
            Burlap Shoes
            Burlap Trousers
            Burnt Bear Meat
            Burnt Chicken
            Burnt Deer Meat
            Burnt Horse Meat
            Burnt Human Meat
            Burnt Pork
            Burnt Wolf Meat
            Butcher Knife
            Cable Tunnel
            Cactus Flesh
            Camera
            Camp Fire
            Can of Beans
            Can of Tuna
            Candle Hat
            Candy Cane
            Candy Cane Club
            CCTV Camera
            Ceiling Light
            Chainlink Fence
            Chainlink Fence Gate
            Chainsaw
            Chair
            Charcoal
            Chinese Lantern
            Chippy Arcade Game
            Chocolate Bar
            Christmas Door Wreath
            Christmas Lights
            Christmas Tree
            Clatter Helmet
            Cloth
            Coal
            Code Lock
            Coffee Can Helmet
            Coffin
            Combat Knife
            Compound Bow
            Concrete Barricade
            Cooked Bear Meat
            Cooked Chicken
            Cooked Deer Meat
            Cooked Fish
            Cooked Horse Meat
            Cooked Human Meat
            Cooked Pork
            Cooked Wolf Meat
            Corn
            Corn Clone
            Corn Seed
            Counter
            Crossbow
            Crude Oil
            Cursed Cauldron
            Custom SMG
            Decorative Baubels
            Decorative Gingerbread Men
            Decorative Pinecones
            Decorative Plastic Candy Canes
            Decorative Tinsel
            Diesel Fuel
            Diving Fins
            Diving Mask
            Diving Tank
            Door Closer
            Door Controller
            Door Key
            Double Barrel Shotgun
            Double Sign Post
            Dragon Door Knocker
            Dragon Mask
            Drop Box
            Duct Tape
            Easter Door Wreath
            Egg Basket
            Electric Fuse
            Electrical Branch
            Empty Can Of Beans
            Empty Propane Tank
            Empty Tuna Can
            Eoka Pistol
            Explosive 5.56 Rifle Ammo
            Explosives
            F1 Grenade
            f-armpit01
            f-eyebrow01
            f-hairstyle-1
            f-hairstyle-2
            f-hairstyle-3
            f-hairstyle-5
            f-pubic01';

        $dict = explode("\n", $dict);
        $line = trim($dict[array_rand($dict)]);
        static $cache = [];
        if ($idx > 0) {
            if (!isset($cache[$idx])) {
                $cache[$idx] = ' i' . $idx . ' ' . $line;
            }
            $line = $cache[$idx];
        }
        return $line;
    }

}
