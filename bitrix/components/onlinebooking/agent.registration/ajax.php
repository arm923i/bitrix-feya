<?
define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$rsGroupList = CGroup::GetList(
	$by = "id",
	$order = "desc",
	Array("STRING_ID" => "AGENT_GROUP")
);
if(intval($rsGroupList->SelectedRowsCount()) > 0)
{
	while($arGroups = $rsGroupList->Fetch())
	   {
		  $arUserGroup = $arGroups;
		  break;
	   }
}
if(isset($arUserGroup) && !empty($arUserGroup)){
	$groupId = $arUserGroup["ID"];
}else{
	$groupId = COption::GetOptionString("gotech.hotelonline", 'USER_AGENT_GROUP');
}

//Добавляем пользователя
$user = new CUser;
$arFields = Array(
	"NAME" => $_POST["USER_NAME"],
	"LAST_NAME" => "",
	"EMAIL" => $_POST["USER_LOGIN"],
	"LOGIN" => $_POST["USER_LOGIN"],
	"ACTIVE" => "Y",
	"GROUP_ID" => array(intval($groupId)),
	"PASSWORD" => $_POST["USER_PASSWORD"],
	"CONFIRM_PASSWORD" => $_POST["USER_PASSWORD"]
);

$userID = $user->Add($arFields);
if (intval($userID) == 0)
	echo $user->LAST_ERROR;
else
	$user->Authorize(intval($userID));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>