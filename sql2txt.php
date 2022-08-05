<?php
/**
* Item_proto and mob_proto converter
*
*
*/

// MySQL/DB Configuration
$config["mysql_host"] = "192.168.5.100";
$config["mysql_user"] = "root";
$config["mysql_pass"] = "";
$config["player_db"] = "player";
$config["item_proto_work"] = "item_proto_WORK"; // Tabela de trabalho
$config["mob_proto_work"] = "mob_proto_WORK"; // Tabela de trabalho

// Output Configuration
$config["prefix_output"] = ""; // Este prefixo é para debug apenas. Exemplo: metin2_ => metin2_item_proto.txt
$config["item_output"] = array("{$config["prefix_output"]}item_names.txt", "{$config["prefix_output"]}item_proto.txt", "{$config["prefix_output"]}item_proto_test.txt");
$config["mob_output"] = array("{$config["prefix_output"]}mob_names.txt", "{$config["prefix_output"]}mob_proto.txt", "{$config["prefix_output"]}mob_proto_test.txt");

class Proto{
private static $dbCon = false;
public static $targets = "";
public static function start(){
if(self::getTargets()){
self::convertTargets();
}
}
private static function output($string, $color, $restoreColor = true, $breakline = true){
switch($color){
case "red": $initColor = "033[31m"; break;
case "green": $initColor = "033[32m"; break;
default: $initColor = "033[0m";
}
$out = $initColor.$string;
if($restoreColor){ $out .= "033[0m"; }
if($breakline){ $out .= "n"; }
echo $out;
}
private static function write($file, $lines){
$fo = fopen($file, "w");
fwrite($fo, $lines);
fclose($fo);
}
private static function connect(){
global $config;
if(!self::$dbCon){
$c = @mysql_connect($config["mysql_host"], $config["mysql_user"], $config["mysql_pass"]) or die(self::output("Erro #1: Ocorreu um erro ao ligar à base de dados.", "red"));
if($c){ self::$dbCon = true; self::output("Ligação à base de dados com sucesso.", "green"); }
}
}
private static function getTargets(){
global $config;
if($_SERVER["argc"] > 1){
$tmp = array();
foreach($_SERVER["argv"] as $k => $v){
if($v == "all"){ $tmp[] = $v; break; }
if($v == "item" || $v == "mob"){ $tmp[] = $v; }
}
if(in_array("all", $tmp)){
self::$targets = "all";
}elseif(in_array("item", $tmp) && in_array("mob", $tmp)){
self::$targets = "all";
}else{
if(in_array("item", $tmp)){ self::$targets = "item"; }
if(in_array("mob", $tmp)){ self::$targets = "mob"; }
}
if(in_array("all", $tmp) || in_array("item", $tmp) || in_array("mob", $tmp)){
return true;
}else{
self::output("Erro #3: Argumento inválido.", "red");
}
}else{
self::output("Erro #2: Precisas de especificar pelo menos um argumento.", "red");
}
self::output("Item/Mob proto Converter", "green");
self::output("", "green");
self::output("Utilização: sh cproto.sh [args...]", "green");
self::output("Argumentos possíveis:", "green");
self::output(" item Converte apenas o {$config["item_proto_work"]}", "green");
self::output(" mob Converte apenas o {$config["mob_proto_work"]}", "green");
self::output(" all Converte tanto o item_proto como o mob_proto", "green");
return false;
}
private static function convertNumToString($target, $value){
$item_type = array(0=>"ITEM_NONE", 1=>"ITEM_WEAPON", 2=>"ITEM_ARMOR", 3=>"ITEM_USE", 4=>"ITEM_AUTOUSE", 5=>"ITEM_MATERIAL", 6=>"ITEM_SPECIAL", 7=>"ITEM_TOOL", 8=>"ITEM_LOTTERY", 9=>"ITEM_ELK", 10=>"ITEM_METIN", 11=>"ITEM_CONTAINER", 12=>"ITEM_FISH", 13=>"ITEM_ROD", 14=>"ITEM_RESOURCE", 15=>"ITEM_CAMPFIRE", 16=>"ITEM_UNIQUE", 17=>"ITEM_SKILLBOOK", 18=>"ITEM_QUEST", 19=>"ITEM_POLYMORPH", 20=>"ITEM_TREASURE_BOX", 21=>"ITEM_TREASURE_KEY", 22=>"ITEM_SKILLFORGET", 23=>"ITEM_GIFTBOX", 24=>"ITEM_PICK", 25=>"ITEM_HAIR", 26=>"ITEM_TOTEM", 27=>"ITEM_BLEND", 28=>"ITEM_COSTUME");
$item_limit = array(0=>"LIMIT_NONE", 1=>"LEVEL", 2=>"STR", 3=>"DEX", 4=>"INT", 5=>"CON", 6=>"PC_BANG", 7=>"REAL_TIME", 8=>"REAL_TIME_FIRST_USE", 9=>"TIMER_BASED_ON_WEAR");
$item_apply = array(0=>"APPLY_NONE", 1=>"APPLY_MAX_HP", 2=>"APPLY_MAX_SP", 3=>"APPLY_CON", 4=>"APPLY_INT", 5=>"APPLY_STR", 6=>"APPLY_DEX", 7=>"APPLY_ATT_SPEED", 8=>"APPLY_MOV_SPEED", 9=>"APPLY_CAST_SPEED", 10=>"APPLY_HP_REGEN", 11=>"APPLY_SP_REGEN", 12=>"APPLY_POISON_PCT", 13=>"APPLY_STUN_PCT", 14=>"APPLY_SLOW_PCT", 15=>"APPLY_CRITICAL_PCT", 16=>"APPLY_PENETRATE_PCT", 17=>"APPLY_ATTBONUS_HUMAN", 18=>"APPLY_ATTBONUS_ANIMAL", 19=>"APPLY_ATTBONUS_ORC", 20=>"APPLY_ATTBONUS_MILGYO", 21=>"APPLY_ATTBONUS_UNDEAD", 22=>"APPLY_ATTBONUS_DEVIL", 23=>"APPLY_STEAL_HP", 24=>"APPLY_STEAL_SP", 25=>"APPLY_MANA_BURN_PCT", 26=>"APPLY_DAMAGE_SP_RECOVER", 27=>"APPLY_BLOCK", 28=>"APPLY_DODGE", 29=>"APPLY_RESIST_SWORD", 30=>"APPLY_RESIST_TWOHAND", 31=>"APPLY_RESIST_DAGGER", 32=>"APPLY_RESIST_BELL", 33=>"APPLY_RESIST_FAN", 34=>"APPLY_RESIST_BOW", 35=>"APPLY_RESIST_FIRE", 36=>"APPLY_RESIST_ELEC", 37=>"APPLY_RESIST_MAGIC", 38=>"APPLY_RESIST_WIND", 39=>"APPLY_REFLECT_MELEE", 40=>"APPLY_REFLECT_CURSE", 41=>"APPLY_POISON_REDUCE", 42=>"APPLY_KILL_SP_RECOVER", 43=>"APPLY_EXP_DOUBLE_BONUS", 44=>"APPLY_GOLD_DOUBLE_BONUS", 45=>"APPLY_ITEM_DROP_BONUS", 46=>"APPLY_POTION_BONUS", 47=>"APPLY_KILL_HP_RECOVER", 48=>"APPLY_IMMUNE_STUN", 49=>"APPLY_IMMUNE_SLOW", 50=>"APPLY_IMMUNE_FALL", 51=>"APPLY_SKILL", 52=>"APPLY_BOW_DISTANCE", 53=>"APPLY_ATT_GRADE_BONUS", 54=>"APPLY_DEF_GRADE_BONUS", 55=>"APPLY_MAGIC_ATT_GRADE", 56=>"APPLY_MAGIC_DEF_GRADE", 57=>"APPLY_CURSE_PCT", 58=>"APPLY_MAX_STAMINA", 59=>"APPLY_ATTBONUS_WARRIOR", 60=>"APPLY_ATTBONUS_ASSASSIN", 61=>"APPLY_ATTBONUS_SURA", 62=>"APPLY_ATTBONUS_SHAMAN", 63=>"APPLY_ATTBONUS_MONSTER", 64=>"APPLY_MALL_ATTBONUS", 65=>"APPLY_MALL_DEFBONUS", 66=>"APPLY_MALL_EXPBONUS", 67=>"APPLY_MALL_ITEMBONUS", 68=>"APPLY_MALL_GOLDBONUS", 69=>"APPLY_MAX_HP_PCT", 70=>"APPLY_MAX_SP_PCT", 71=>"APPLY_SKILL_DAMAGE_BONUS", 72=>"APPLY_NORMAL_HIT_DAMAGE_BONUS", 73=>"APPLY_SKILL_DEFEND_BONUS", 74=>"APPLY_NORMAL_HIT_DEFEND_BONUS", 75=>"APPLY_PC_BANG_EXP_BONUS", 76=>"APPLY_PC_BANG_DROP_BONUS", 77=>"APPLY_EXTRACT_HP_PCT", 78=>"APPLY_RESIST_WARRIOR", 79=>"APPLY_RESIST_ASSASSIN", 80=>"APPLY_RESIST_SURA", 81=>"APPLY_RESIST_SHAMAN", 82=>"APPLY_ENERGY", 83=>"APPLY_DEF_GRADE", 84=>"APPLY_COSTUME_ATTR_BONUS", 85=>"APPLY_MAGIC_ATTBONUS_PER", 86=>"APPLY_MELEE_MAGIC_ATTBONUS_PER");
$mob_rank = array(0=>"PAWN", 1=>"S_PAWN", 2=>"KNIGHT", 3=>"S_KNIGHT", 4=>"BOSS", 5=>"KING");
$mob_type = array(0=>"MONSTER", 1=>"NPC", 2=>"STONE", 3=>"WARP", 4=>"DOOR", 5=>"BUILDING", 7=>"POLYMORPH_PC", 8=>"HORSE", 9=>"GOTO");
$mob_battletype = array(0=>"MELEE", 1=>"RANGE", 2=>"MAGIC", 3=>"SPECIAL", 4=>"POWER", 5=>"TANKER");
switch($target){
case "item_type": $target = $item_type; break;
case "item_limit": $target = $item_limit; break;
case "item_apply": $target = $item_apply; break;
case "mob_rank": $target = $mob_rank; break;
case "mob_type": $target = $mob_type; break;
case "mob_battletype": $target = $mob_battletype; break;
default: $target = "";
}
return $target[$value];
}
private static function getItemSubtype($type, $subtype){
if($type == 1){
if($subtype == 0){ return ""WEAPON_SWORD""; }
elseif($subtype == 1){ return ""WEAPON_DAGGER""; }
elseif($subtype == 2){ return ""WEAPON_BOW""; }
elseif($subtype == 3){ return ""WEAPON_TWO_HANDED""; }
elseif($subtype == 4){ return ""WEAPON_BELL""; }
elseif($subtype == 5){ return ""WEAPON_FAN""; }
elseif($subtype == 6){ return ""WEAPON_ARROW""; }
elseif($subtype == 7){ return ""WEAPON_MOUNT_SPEAR""; }
elseif($subtype == 8){ return ""WEAPON_NUM_TYPES""; }
}elseif($type == 2){
if($subtype == 0){ return ""ARMOR_BODY""; }
elseif($subtype == 1){ return ""ARMOR_HEAD""; }
elseif($subtype == 2){ return ""ARMOR_SHIELD""; }
elseif($subtype == 3){ return ""ARMOR_WRIST""; }
elseif($subtype == 4){ return ""ARMOR_FOOTS""; }
elseif($subtype == 5){ return ""ARMOR_NECK""; }
elseif($subtype == 6){ return ""ARMOR_EAR""; }
elseif($subtype == 7){ return ""ARMOR_NUM_TYPES""; }
}elseif($type == 3){
if($subtype == 0){ return ""USE_POTION""; }
elseif($subtype == 1){ return ""USE_TALISMAN""; }
elseif($subtype == 2){ return ""USE_TUNING""; }
elseif($subtype == 3){ return ""USE_MOVE""; }
elseif($subtype == 4){ return ""USE_TREASURE_BOX""; }
elseif($subtype == 5){ return ""USE_MONEYBAG""; }
elseif($subtype == 6){ return ""USE_BAIT""; }
elseif($subtype == 7){ return ""USE_ABILITY_UP""; }
elseif($subtype == 8){ return ""USE_AFFECT""; }
elseif($subtype == 9){ return ""USE_CREATE_STONE""; }
elseif($subtype == 10){ return ""USE_SPECIAL""; }
elseif($subtype == 11){ return ""USE_POTION_NODELAY""; }
elseif($subtype == 12){ return ""USE_CLEAR""; }
elseif($subtype == 13){ return ""USE_INVISIBILITY""; }
elseif($subtype == 14){ return ""USE_DETACHMENT""; }
elseif($subtype == 15){ return ""USE_BUCKET""; }
elseif($subtype == 17){ return ""USE_CLEAN_SOCKET""; }
elseif($subtype == 18){ return ""USE_CHANGE_ATTRIBUTE""; }
elseif($subtype == 19){ return ""USE_ADD_ATTRIBUTE""; }
elseif($subtype == 20){ return ""USE_ADD_ACCESSORY_SOCKET""; }
elseif($subtype == 21){ return ""USE_PUT_INTO_ACCESSORY_SOCKET""; }
elseif($subtype == 22){ return ""USE_ADD_ATTRIBUTE2""; }
elseif($subtype == 23){ return ""USE_RECIPE""; }
elseif($subtype == 24){ return ""USE_CHANGE_ATTRIBUTE2""; }
elseif($subtype == 25){ return ""USE_BIND""; }
elseif($subtype == 26){ return ""USE_UNBIND""; }
elseif($subtype == 29){ return ""AUTOUSE_BOMB""; }
elseif($subtype == 30){ return ""AUTOUSE_GOLD""; }
}elseif($type == 5){
if($subtype == 0){ return ""MATERIAL_LEATHER""; }
elseif($subtype == 1){ return ""MATERIAL_BLOOD""; }
elseif($subtype == 2){ return ""MATERIAL_ROOT""; }
elseif($subtype == 3){ return ""MATERIAL_NEEDLE""; }
elseif($subtype == 4){ return ""MATERIAL_JEWEL""; }
}elseif($type == 6){
if($subtype == 0){ return ""SPECIAL_MAP""; }
if($subtype == 1){ return ""SPECIAL_KEY""; }
if($subtype == 2){ return ""SPECIAL_DOC""; }
if($subtype == 3){ return ""SPECIAL_SPIRIT""; }
if($subtype == 4){ return ""SPECIAL_MAP""; }
}elseif($type == 7){
if($subtype == 0){ return ""TOOL_FISHING_ROD""; }
}elseif($type == 8){
if($subtype == 0){ return ""LOTTERY_TICKET""; }
elseif($subtype == 1){ return ""LOTTERY_INSTANT""; }
}elseif($type == 10){
if($subtype == 0){ return ""METIN_NORMAL""; }
elseif($subtype == 1){ return ""METIN_GOLD""; }
}elseif($type == 12){
if($subtype == 0){ return ""FISH_ALIVE""; }
elseif($subtype == 1){ return ""FISH_DEAD""; }
}elseif($type == 14){
if($subtype == 0){ return ""RESOURCE_FISHBONE""; }
elseif($subtype == 1){ return ""RESOURCE_WATERSTONEPIECE""; }
elseif($subtype == 2){ return ""RESOURCE_WATERSTONE""; }
elseif($subtype == 3){ return ""RESOURCE_BLOOD_PEARL""; }
elseif($subtype == 4){ return ""RESOURCE_BLUE_PEARL""; }
elseif($subtype == 5){ return ""RESOURCE_WHITE_PEARL""; }
elseif($subtype == 6){ return ""RESOURCE_BUCKET""; }
elseif($subtype == 7){ return ""RESOURCE_CRYSTAL""; }
elseif($subtype == 8){ return ""RESOURCE_GEM""; }
elseif($subtype == 9){ return ""RESOURCE_STONE""; }
elseif($subtype == 10){ return ""RESOURCE_METIN""; }
elseif($subtype == 11){ return ""RESOURCE_ORE""; }
}elseif($type == 16){
if($subtype == 0){ return ""UNIQUE_NONE""; }
elseif($subtype == 2){ return ""UNIQUE_SPECIAL_RIDE""; }
elseif($subtype == 3){ return ""UNIQUE_3""; }
elseif($subtype == 4){ return ""UNIQUE_4""; }
elseif($subtype == 5){ return ""UNIQUE_5""; }
elseif($subtype == 6){ return ""UNIQUE_6""; }
elseif($subtype == 7){ return ""UNIQUE_7""; }
elseif($subtype == 8){ return ""UNIQUE_8""; }
elseif($subtype == 9){ return ""UNIQUE_9""; }
elseif($subtype == 10){ return ""USE_SPECIAL""; }
}elseif($type == 28){
if($subtype == 0){ return ""COSTUME_BODY""; }
elseif($subtype == 1){ return ""COSTUME_HAIR""; }
}
return 0;
}
private static function getFlags($flag, $method){
$antiflags = array(0=>"NONE", 1=>"ANTI_FEMALE", 2=>"ANTI_MALE", 4=>"ANTI_MUSA", 8=>"ANTI_ASSASSIN", 16=>"ANTI_SURA", 32=>"ANTI_MUDANG", 64=>"ANTI_GET", 128=>"ANTI_DROP", 256=>"ANTI_SELL", 512=>"ANTI_EMPIRE_A", 1024=>"ANTI_EMPIRE_B", 2048=>"ANTI_EMPIRE_C", 4096=>"ANTI_SAVE", 8192=>"ANTI_GIVE", 16384=>"ANTI_PKDROP", 32768=>"ANTI_STACK", 65536=>"ANTI_MYSHOP", 131072=>"ANTI_SAFEBOX");
$flags = array(0=>"NONE", 1=>"ITEM_TUNABLE", 2=>"ITEM_SAVE", 4=>"ITEM_STACKABLE", 8=>"COUNT_PER_1GOLD", 16=>"ITEM_SLOW_QUERY", 32=>"ITEM_UNIQUE", 64=>"ITEM_MAKECOUNT", 128=>"ITEM_IRREMOVABLE", 256=>"CONFIRM_WHEN_USE", 512=>"QUEST_USE", 1024=>"QUEST_USE_MULTIPLE", 2048=>"QUEST_GIVE", 4096=>"ITEM_QUEST", 8192=>"LOG", 16384=>"STACKABLE", 32768=>"32768", 65536=>"REFINEABLE", 131072=>"131072", 262144=>"ITEM_APPLICABLE");
$wearflags = array(0=>"NONE", 1=>"WEAR_BODY", 2=>"WEAR_HEAD", 4=>"WEAR_FOOTS", 8=>"WEAR_WRIST", 16=>"WEAR_WEAPON", 32=>"WEAR_NECK", 64=>"WEAR_EAR", 128=>"WEAR_SHIELD", 256=>"WEAR_UNIQUE", 512=>"WEAR_ARROW", 1024=>"WEAR_HAIR", 2048=>"WEAR_ABILITY");
switch($method){
case "af": $target = $antiflags; break;
case "f": $target = $flags; break;
case "wf": $target = $wearflags; break;
default: $target = "";
}
$str = """;
for($i=17;$i>=0;$i--){
$pow = pow(2, $i);
if($pow < $flag){
$str = " | ".$target[$pow].$str;
$flag = $flag-$pow;
}elseif($pow == $flag){
return """.$target[$pow].$str;
}
}
return ""NONE"";
}
private static function convertTargets(){
global $config;
self::connect();
self::output("Item/Mob proto Converter", "green");
self::output("", "green");
if(self::$targets == "all" || self::$targets == "item"){
self::output("A converter {$config["item_proto_work"]}...", "green");
$write = array("", "", "");
$write[0] .= "VNUM LOCALE_NAMEn";
$write[1] .= ""Vnum" "Name" "Type" "SubType" "Size" "AntiFlags" "Flags" "WearFlags" "ImmuneFlags" "Gold" "ShopBuyPrice" "RefinedVnum" "RefineSet" "AlterToMagicItemPercent" "LimitType0" "LimitValue0" "LimitType1" "LimitValue1" "ApplyType0" "ApplyValue0" "ApplyType1" "ApplyValue1" "ApplyType2" "ApplyValue2" "Value0" "Value1" "Value2" "Value3" "Value4" "Value5" "Specular" "GainSocketPercent" "AddonType"n";
$write[2] .= ""Vnum" "Name" "Type" "SubType" "Size" "AntiFlags" "Flags" "WearFlags" "ImmuneFlags" "Gold" "ShopBuyPrice" "RefinedVnum" "RefineSet" "AlterToMagicItemPercent" "LimitType0" "LimitValue0" "LimitType1" "LimitValue1" "ApplyType0" "ApplyValue0" "ApplyType1" "ApplyValue1" "ApplyType2" "ApplyValue2" "Value0" "Value1" "Value2" "Value3" "Value4" "Value5" "Specular" "GainSocketPercent" "AddonType"n";
$query = mysql_query("SELECT * FROM {$config["player_db"]}.{$config["item_proto_work"]} ORDER BY vnum ASC");
while($fetch = mysql_fetch_assoc($query)){
$type = self::convertNumToString("item_type", $fetch["type"]);
$subtype = self::getItemSubtype($fetch["type"], $fetch["subtype"]);
$antiflag = self::getFlags($fetch["antiflag"], "af");
$flag = self::getFlags($fetch["flag"], "f");
$wearflag = self::getFlags($fetch["wearflag"], "wf");
$immuneflag = (empty($fetch["immuneflag"])) ? ""NONE"" : ""{$fetch["immuneflag"]}"";
$limittype0 = self::convertNumToString("item_limit", $fetch["limittype0"]);
$limittype1 = self::convertNumToString("item_limit", $fetch["limittype1"]);
$applytype0 = self::convertNumToString("item_apply", $fetch["applytype0"]);
$applytype1 = self::convertNumToString("item_apply", $fetch["applytype1"]);
$applytype2 = self::convertNumToString("item_apply", $fetch["applytype2"]);
$write[0] .= "{$fetch["vnum"]} {$fetch["locale_name"]}n";
$write[1] .= "{$fetch["vnum"]} "{$fetch["name"]}" "{$type}" {$subtype} {$fetch["size"]} {$antiflag} {$flag} {$wearflag} {$immuneflag} {$fetch["gold"]} {$fetch["shop_buy_price"]} {$fetch["refined_vnum"]} {$fetch["refine_set"]} {$fetch["magic_pct"]} "{$limittype0}" {$fetch["limitvalue0"]} "{$limittype1}" {$fetch["limitvalue1"]} "{$applytype0}" {$fetch["applyvalue0"]} "{$applytype1}" {$fetch["applyvalue1"]} "{$applytype2}" {$fetch["applyvalue2"]} {$fetch["value0"]} {$fetch["value1"]} {$fetch["value2"]} {$fetch["value3"]} {$fetch["value4"]} {$fetch["value5"]} {$fetch["specular"]} {$fetch["socket_pct"]} {$fetch["addon_type"]}n";
$write[2] .= "{$fetch["vnum"]} "{$fetch["name"]}" "{$type}" {$subtype} {$fetch["size"]} {$antiflag} {$flag} {$wearflag} {$immuneflag} {$fetch["gold"]} {$fetch["shop_buy_price"]} {$fetch["refined_vnum"]} {$fetch["refine_set"]} {$fetch["magic_pct"]} "{$limittype0}" {$fetch["limitvalue0"]} "{$limittype1}" {$fetch["limitvalue1"]} "{$applytype0}" {$fetch["applyvalue0"]} "{$applytype1}" {$fetch["applyvalue1"]} "{$applytype2}" {$fetch["applyvalue2"]} {$fetch["value0"]} {$fetch["value1"]} {$fetch["value2"]} {$fetch["value3"]} {$fetch["value4"]} {$fetch["value5"]} {$fetch["specular"]} {$fetch["socket_pct"]} {$fetch["addon_type"]}n";
}
for($i=0;$i<2;$i++){
self::output(" -> A escrever {$config["item_output"][$i]}...", "green");
self::write($config["item_output"][$i], $write[$i]);
}
self::output("{$config["item_proto_work"]} convertido com sucesso!", "green");
}
if(self::$targets == "all" || self::$targets == "mob"){
self::output("A converter {$config["mob_proto_work"]}...", "green");
$write = array("", "", "");
$write[0] .= "VNUM LOCALE_NAMEn";
$write[1] .= ""Vnum" "Name" "Rank" "Type" "BattleType" "Level" "Size" "AiFlags" "MountCapacity" "RaceFlags" "ImmuneFlags" "Empire" "Folder" "OnClick" "St" "Dx" "Ht" "Iq" "MinDamage" "MaxDamage" "MaxHp" "RegenCycle" "RegenPercent" "MinGold" "MaxGold" "Exp" "Def" "AttackSpeed" "MoveSpeed" "AggressiveHpPct" "AggressiveSight" "AttackRange" "DropItemGroup" "ResurrectionVnum" "EnchantCurse" "EnchantSlow" "EnchantPoison" "EnchantStun" "EnchantCritical" "EnchantPenetrate" "ResistSword" "ResistTwoHanded" "ResistDagger" "ResistBell" "ResistFan" "ResistBow" "ResistFire" "ResistElect" "ResistMagic" "ResistWind" "ResistPoison" "DamMultiply" "SummonVnum" "DrainSp" "MobColor" "PolymorphItem" "SkillLevel0" "SkillVnum0" "SkillLevel1" "SkillVnum1" "SkillLevel2" "SkillVnum2" "SkillLevel3" "SkillVnum3" "SkillLevel4" "SkillVnum4" "SpBerserk" "SpStoneskin" "SpGodspeed" "SpDeathblow" "SpRevive"n";
$write[2] .= ""Vnum" "Name" "Rank" "Type" "BattleType" "Level" "Size" "AiFlags" "MountCapacity" "RaceFlags" "ImmuneFlags" "Empire" "Folder" "OnClick" "St" "Dx" "Ht" "Iq" "MinDamage" "MaxDamage" "MaxHp" "RegenCycle" "RegenPercent" "MinGold" "MaxGold" "Exp" "Def" "AttackSpeed" "MoveSpeed" "AggressiveHpPct" "AggressiveSight" "AttackRange" "DropItemGroup" "ResurrectionVnum" "EnchantCurse" "EnchantSlow" "EnchantPoison" "EnchantStun" "EnchantCritical" "EnchantPenetrate" "ResistSword" "ResistTwoHanded" "ResistDagger" "ResistBell" "ResistFan" "ResistBow" "ResistFire" "ResistElect" "ResistMagic" "ResistWind" "ResistPoison" "DamMultiply" "SummonVnum" "DrainSp" "MobColor" "PolymorphItem" "SkillLevel0" "SkillVnum0" "SkillLevel1" "SkillVnum1" "SkillLevel2" "SkillVnum2" "SkillLevel3" "SkillVnum3" "SkillLevel4" "SkillVnum4" "SpBerserk" "SpStoneskin" "SpGodspeed" "SpDeathblow" "SpRevive"n";
$query = mysql_query("SELECT * FROM {$config["player_db"]}.{$config["mob_proto_work"]} ORDER BY vnum ASC");
while($fetch = mysql_fetch_assoc($query)){
$rank = self::convertNumToString("mob_rank", $fetch["rank"]);
$type = self::convertNumToString("mob_type", $fetch["type"]);
$battletype = self::convertNumToString("mob_battletype", $fetch["battle_type"]);
$ai_flag = (empty($fetch["ai_flag"])) ? "" : ""{$fetch["ai_flag"]}"";
$setRaceFlag = (empty($fetch["setRaceFlag"])) ? "" : ""{$fetch["setRaceFlag"]}"";
$setImmuneFlag = (empty($fetch["setImmuneFlag"])) ? "" : ""{$fetch["setImmuneFlag"]}"";
$mob_color = (empty($fetch["mob_color"])) ? "0" : "{$fetch["mob_color"]}";
$write[0] .= "{$fetch["vnum"]} {$fetch["locale_name"]}n";
$write[1] .= "{$fetch["vnum"]} "{$fetch["name"]}" "{$rank}" "{$type}" "{$battletype}" {$fetch["level"]} {$fetch["size"]} {$ai_flag} {$fetch["mount_capacity"]} {$setRaceFlag} {$setImmuneFlag} {$fetch["empire"]} "{$fetch["folder"]}" {$fetch["on_click"]} {$fetch["st"]} {$fetch["dx"]} {$fetch["ht"]} {$fetch["iq"]} {$fetch["damage_min"]} {$fetch["damage_max"]} {$fetch["max_hp"]} {$fetch["regen_cycle"]} {$fetch["regen_percent"]} {$fetch["gold_min"]} {$fetch["gold_max"]} {$fetch["exp"]} {$fetch["def"]} {$fetch["attack_speed"]} {$fetch["move_speed"]} {$fetch["aggressive_hp_pct"]} {$fetch["aggressive_sight"]} {$fetch["attack_range"]} {$fetch["drop_item"]} {$fetch["resurrection_vnum"]} {$fetch["enchant_curse"]} {$fetch["enchant_slow"]} {$fetch["enchant_poison"]} {$fetch["enchant_stun"]} {$fetch["enchant_critical"]} {$fetch["enchant_penetrate"]} {$fetch["resist_sword"]} {$fetch["resist_twohand"]} {$fetch["resist_dagger"]} {$fetch["resist_bell"]} {$fetch["resist_fan"]} {$fetch["resist_bow"]} {$fetch["resist_fire"]} {$fetch["resist_elect"]} {$fetch["resist_magic"]} {$fetch["resist_wind"]} {$fetch["resist_poison"]} {$fetch["dam_multiply"]} {$fetch["summon"]} {$fetch["drain_sp"]} {$mob_color} {$fetch["polymorph_item"]} {$fetch["skill_level0"]} {$fetch["skill_vnum0"]} {$fetch["skill_level1"]} {$fetch["skill_vnum1"]} {$fetch["skill_level2"]} {$fetch["skill_vnum2"]} {$fetch["skill_level3"]} {$fetch["skill_vnum3"]} {$fetch["skill_level4"]} {$fetch["skill_vnum4"]} {$fetch["sp_berserk"]} {$fetch["sp_stoneskin"]} {$fetch["sp_godspeed"]} {$fetch["sp_deathblow"]} {$fetch["sp_revive"]}n";
$write[2] .= "{$fetch["vnum"]} "{$fetch["name"]}" "{$rank}" "{$type}" "{$battletype}" {$fetch["level"]} {$fetch["size"]} {$ai_flag} {$fetch["mount_capacity"]} {$setRaceFlag} {$setImmuneFlag} {$fetch["empire"]} "{$fetch["folder"]}" {$fetch["on_click"]} {$fetch["st"]} {$fetch["dx"]} {$fetch["ht"]} {$fetch["iq"]} {$fetch["damage_min"]} {$fetch["damage_max"]} {$fetch["max_hp"]} {$fetch["regen_cycle"]} {$fetch["regen_percent"]} {$fetch["gold_min"]} {$fetch["gold_max"]} {$fetch["exp"]} {$fetch["def"]} {$fetch["attack_speed"]} {$fetch["move_speed"]} {$fetch["aggressive_hp_pct"]} {$fetch["aggressive_sight"]} {$fetch["attack_range"]} {$fetch["drop_item"]} {$fetch["resurrection_vnum"]} {$fetch["enchant_curse"]} {$fetch["enchant_slow"]} {$fetch["enchant_poison"]} {$fetch["enchant_stun"]} {$fetch["enchant_critical"]} {$fetch["enchant_penetrate"]} {$fetch["resist_sword"]} {$fetch["resist_twohand"]} {$fetch["resist_dagger"]} {$fetch["resist_bell"]} {$fetch["resist_fan"]} {$fetch["resist_bow"]} {$fetch["resist_fire"]} {$fetch["resist_elect"]} {$fetch["resist_magic"]} {$fetch["resist_wind"]} {$fetch["resist_poison"]} {$fetch["dam_multiply"]} {$fetch["summon"]} {$fetch["drain_sp"]} {$mob_color} {$fetch["polymorph_item"]} {$fetch["skill_level0"]} {$fetch["skill_vnum0"]} {$fetch["skill_level1"]} {$fetch["skill_vnum1"]} {$fetch["skill_level2"]} {$fetch["skill_vnum2"]} {$fetch["skill_level3"]} {$fetch["skill_vnum3"]} {$fetch["skill_level4"]} {$fetch["skill_vnum4"]} {$fetch["sp_berserk"]} {$fetch["sp_stoneskin"]} {$fetch["sp_godspeed"]} {$fetch["sp_deathblow"]} {$fetch["sp_revive"]}n";
}
for($i=0;$i<2;$i++){
self::output(" -> A escrever {$config["mob_output"][$i]}...", "green");
self::write($config["mob_output"][$i], $write[$i]);
}
self::output("{$config["mob_proto_work"]} convertido com sucesso!", "green");
}
}
}
Proto::start();
?>