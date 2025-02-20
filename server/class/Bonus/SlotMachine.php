<?php
namespace Cls\Bonus;

use Schema\SlotMachines;
use Cls\Reward;
use Schema\Character;
use Srv\Config;

/* SYMBOLS
public static const Unknown:int = 0;
public static const Coins:int = 1;
public static const Item:int = 2;
public static const Booster:int = 3;
public static const Sidekick:int = 4;
public static const StatPoints:int = 5;
public static const Xp:int = 6;
public static const Energy:int = 7;
public static const Training:int = 8;
*/

/* REWARD QUALITY
public static const Basic:int = 1;
public static const Special:int = 2;
public static const Great:int = 3;
*/

class SlotMachine{
    
    public static function spinSlotMachine($player){
        $quality = 3;//mt_rand(1,3);
        $slot1 = 1;//mt_rand(1,8);
        $slot2 = 1;//mt_rand(1,8);
        $slot3 = 1;//mt_rand(1,8);
        $reward = new Reward();
        $won = false;
        if($slot1 == $slot2 && $slot2 == $slot3) $won = true;
        if(random() < 0.2)
            $reward->xp($player->getLVL() * Config::get('constants.slotmachine_xp_reward_base_time') * $quality * random(0.05,0.07));
        else
            $reward->coins($player->getLVL() * Config::get('constants.slotmachine_coin_reward_base_time') * $quality * random(0.05,0.1));
        $machine = new SlotMachines([
            'character_id'=>$player->character->id,
            'slotmachine_reward_quality'=>$quality,
            'slotmachine_slot1'=>$slot1,
            'slotmachine_slot2'=>$slot2,
            'slotmachine_slot3'=>$slot3,
            'reward'=>$reward->toJSON(),
            'slot'=>$slot1,
            'won'=>$won,
            'timestamp'=>time()
        ]);
        $machine->save();
        return $machine;
    }
    
    public static function countCurrentSpins($player){
        return SlotMachines::count(function($q)use($player){$q->where('character_id',$player->character->id)->where('history',0);});
    }
    
    public static function findCurrentReward($player){
        return SlotMachines::find(function($q)use($player){$q->where('character_id',$player->character->id)->where('history',0)->orderBy('timestamp');});
    }

    public static function getLastWins(){
        $returned_data = [];

        $history = SlotMachines::findAll(function($q){
            $q->where('history',1)->where('won',1)->orderBy('timestamp')->limit(10);
        });
  
            foreach ($history as $person) {
                $chid = $person->character_id;

                $char = Character::find(function($q) use($chid){ 
                    $q->where('user_id',$chid); 
                });

                $slot = $person->slot + 400; //+400 cuz playata
                array_push($returned_data, [
                    'character_gender'=>$char->gender,
                    'character_level'=>$char->level,
                    'character_name'=>$char->name,
                    'character_id'=>$char->user_id,
                    'type'=>$slot,
                    'value1'=>current(json_decode($person->reward)),
                    'timestamp'=>time()
                ]);
            }

        return $returned_data;
    }
}