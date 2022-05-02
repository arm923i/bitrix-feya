<?define('NO_KEEP_STATISTIC', true);
define('NO_AGENT_STATISTIC', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule("iblock");

$start = true;
if (isset($_REQUEST["f_iblock"]) && !empty($_REQUEST["f_iblock"]) && isset($_REQUEST["code"]) && !empty($_REQUEST["code"])) {
    $start = false;
    if (isset($_REQUEST["link_iblock"]) && !empty($_REQUEST["link_iblock"])) {
        $pr = CIBlockProperty::GetList(array(), array("IBLOCK_ID" => $_REQUEST["f_iblock"], "CODE" => substr($_REQUEST["code"], 9)));
        $property = array(
            "LINK_IBLOCK_ID" => $_REQUEST["link_iblock"]
        );
        if($prop_fields = $pr->GetNext()) {
            $ibp = new CIBlockProperty;
            if(!$ibp->Update($prop_fields["ID"], $property))
                var_dump('Обновление поля прошло неудачно. Скопируйте адресную строку и отправьте ее разработчикам');
            else $PropID = $prop_fields["ID"];
        }
    }
    if (isset($_REQUEST["value"]) && !empty($_REQUEST["value"])) {
        $elements = CIBlockElement::GetList(
            array(),
            array("IBLOCK_ID" => $_REQUEST["f_iblock"]),
            false,
            false,
            array("NAME", "ID")
        );
        if ($_REQUEST["value"] !== 'all') {
            while($el = $elements->GetNext()) {
                $PROP = array();
                $PROP[substr($_REQUEST["code"], 9)] = $_REQUEST["value"];
                CIBlockElement::SetPropertyValuesEx($el["ID"], false, $PROP);
            }
        } else {
            $link_elements = CIBlockElement::GetList(
                array(),
                array("IBLOCK_ID" => $_REQUEST["link_iblock"]),
                false,
                false,
                array("NAME", "ID")
            );
            $link_el_arr = array();
            while($link_el = $link_elements->GetNext()) {
                $link_el_arr[] = $link_el["ID"];
            }
            while($el = $elements->GetNext()) {
                $PROP = array();
                $PROP[substr($_REQUEST["code"], 9)] = $link_el_arr;
                CIBlockElement::SetPropertyValuesEx($el["ID"], false, $PROP);
            }
        }
    }
    echo "<br><h3>Операция завершена</h3>";
} else {
    $arTypesEx = CIBlockParameters::GetIBlockTypes(Array("-"=>" "));

    foreach($arTypesEx as $key_type => $iblockType) {
        $db_iblock = CIBlock::GetList(Array("SORT" => "ASC"), Array("TYPE" => $key_type));
        while($arRes = $db_iblock->Fetch()) {
            $arIBlocks[] = array("TYPE" => $key_type, "ID" => $arRes["ID"], "NAME" => $arRes["NAME"]);
            $arProperty[] = array("IBLOCK_ID" => $arRes["ID"], "NAME" => GetMessage("NEW_PROPERTY"), "CODE" => 0);
            unset($fields);
            unset($properties);
            unset($property);
            unset($ex);
            $properties = CIBlock::GetProperties($arRes["ID"], array("SORT" => "ASC"), array());
            while($property = $properties->GetNext()) {
                $arProperty[] = array("IBLOCK_ID" => $arRes["ID"], "PROPERTY_TYPE" => $property["PROPERTY_TYPE"], "MULTIPLE" => $property["MULTIPLE"], "USER_TYPE" => $property["USER_TYPE"], "LINK_IBLOCK_ID" => $property["LINK_IBLOCK_ID"], "NAME" => "[PROPERTY_".$property["CODE"]."] ".$property["NAME"], "CODE" => "PROPERTY_".$property["CODE"]);
            }
        }
    }
    if (count($arIBlocks) == 0) {
        echo "<h3 style='color: red'>Похоже вы не авторизованы</h3>";
    }
}
?>
<?if($start):?>
    Выберите тип инфоблока:
    <br>
    <select name="iblock_type" id="iblock_type" onChange="changeSel(this); changeSelIblock(this);">
        <?foreach($arTypesEx as $key => $iblock_type):?>
            <option value="<?=$key?>"><?=$iblock_type?></option>
        <?endforeach;?>
    </select>
    <br>
    <br>
    Выберите инфоблок:
    <br>
    <select name="iblocks" id="iblocks" onChange="changeSelIblock(this);">
        <option value="0">&nbsp;</option>
    </select>
    <br>
    <br>
    Выберите свойство инфоблока:
    <br>
    <select name="properties" id="properties" onChange="changeSelProperty(event, this)">
        <option value="0">&nbsp;</option>
    </select>

    <br>
    <br>

    <hr>

    <br>
    <br>

    <label id="string_value_label" style="display:none">
        Введите необходимое значение:
        <br>
        <input type="text" name="string_value" id="string_value">
    </label>
    <label id="sel_iblocks_label" style="display:none">
        Выберите инфоблок, с которым связано выбранное поле:
        <br>
        <select name="sel_iblocks" id="sel_iblocks" onChange="changeSelResultIblock(this)">
            <option value="0">&nbsp;</option>
        </select>
    </label>
    <br>
    <label id="iblock_id_value_label" style="display:none">
        Введите необходимое значение (ID элемента инфоблока):
        <br>
        <input type="text" name="iblock_id_value" id="iblock_id_value">
    </label>
    <label id="iblock_id_check_all_label" style="display:none">
        Выбрать все элементы:
        <br>
        <input type="checkbox" name="iblock_id_check_all" id="iblock_id_check_all">
    </label>

    <br>
    <br>

    <div id="save_wrapper" style="display: none">
        <hr>

        <br>
        <br>

        <form method="GET" id="save_form">
            <input type="hidden" name="f_iblock">
            <input type="hidden" name="code">
            <input type="hidden" name="link_iblock">
            <input type="hidden" name="value">
        </form>
        <button id="save" onClick="clickSaveBtn(this)">Записать</button>
    </div>

    <script type="text/javascript">
        var iblocks = new Array();
        <?foreach($arIBlocks as $iblock):?>
        iblocks[iblocks.length] = {
            "type": "<?=$iblock["TYPE"]?>",
            "id": "<?=$iblock["ID"]?>",
            "name": "<?=$iblock["NAME"]?>"
        };
        <?endforeach;?>

        var properties = new Array();
        <?foreach($arProperty as $property):?>
        properties[properties.length] = {
            "iblock": "<?=$property["IBLOCK_ID"]?>",
            "code": "<?=$property["CODE"]?>",
            "name": "<?=$property["NAME"]?>",
            "property_type": "<?=$property["PROPERTY_TYPE"]?>",
            "user_type": "<?=$property["USER_TYPE"]?>",
            "multiple": "<?=$property["MULTIPLE"]?>",
            "link_iblock_id": "<?=$property["LINK_IBLOCK_ID"]?>",
        };
        <?endforeach;?>

        function clickSaveBtn(btn) {
            document.querySelector('#save_form [name="f_iblock"]').value = document.getElementById('iblocks').options[document.getElementById('iblocks').selectedIndex].value;
            document.querySelector('#save_form [name="code"]').value = document.getElementById('properties').options[document.getElementById('properties').selectedIndex].value;
            if (document.getElementById('sel_iblocks_label').style.display == 'none') {
                document.querySelector('#save_form [name="link_iblock"]').value = "";
                document.querySelector('#save_form [name="value"]').value = document.getElementById('string_value').value;
            } else {
                document.querySelector('#save_form [name="link_iblock"]').value = document.getElementById('sel_iblocks').options[document.getElementById('sel_iblocks').selectedIndex].value;
                if (document.getElementById('iblock_id_check_all').checked) {
                    document.querySelector('#save_form [name="value"]').value = 'all';
                } else {
                    document.querySelector('#save_form [name="value"]').value = document.getElementById('iblock_id_value').value;
                }
            }
            document.querySelector('#save_form').submit();
        }

        function changeSel(obj) {
            var iblock_type = obj.options[obj.selectedIndex].value;
            var objSel = document.getElementById('iblocks');
            objSel.options.length = 0;
            for(var i=0; i<iblocks.length; i++)
                if(iblocks[i]["type"] == iblock_type)
                    objSel.options[objSel.options.length] = new Option(iblocks[i]["name"], iblocks[i]["id"]);
        }
        function changeSelIblock(obj) {
            var iblock = document.getElementById('iblocks').options[document.getElementById('iblocks').selectedIndex].value;
            if(iblock) {
                var objSel = document.getElementById('properties');
                removeOptions(objSel);
                for(var i=0; i<properties.length; i++) {
                    if(properties[i]["iblock"] == iblock){
                        objSel.options[objSel.options.length] = new Option(properties[i]["name"], properties[i]["code"]);
                    }
                }
            }
        }

        function changeSelProperty(e, obj) {
            e.preventDefault();
            var property_code = obj.options[obj.selectedIndex].value;
            var iblock = document.getElementById('iblocks').options[document.getElementById('iblocks').selectedIndex].value;
            var iblock_type = document.getElementById('iblock_type').options[document.getElementById('iblock_type').selectedIndex].value;

            document.querySelector("#string_value_label").style.display = 'none';
            document.querySelector("#sel_iblocks_label").style.display = 'none';
            document.querySelector("#iblock_id_check_all_label").style.display = 'none';
            document.querySelector("#iblock_id_value_label").style.display = 'none';
            document.querySelector("#save_wrapper").style.display = 'none';

            for(var i=0; i<properties.length; i++) {
                if(properties[i]["iblock"] == iblock && properties[i]["code"] == property_code){
                    console.log(properties[i]);
                    if (properties[i].property_type == 'S') {
                        document.querySelector("#string_value_label").style.display = 'block';
                    }
                    if (properties[i].property_type == 'E') {
                        var objSel = document.querySelector("#sel_iblocks");
                        removeOptions(objSel);
                        for(var j=0; j<iblocks.length; j++) {
                            if(iblocks[j]["type"] == iblock_type) {
                                if(iblocks[j]["id"] == properties[i].link_iblock_id){
                                    objSel.options[objSel.options.length] = new Option(iblocks[j]["name"], iblocks[j]["id"], false, true);
                                } else {
                                    objSel.options[objSel.options.length] = new Option(iblocks[j]["name"], iblocks[j]["id"]);
                                }
                            }
                        }
                        document.querySelector("#sel_iblocks_label").style.display = 'block';
                        if (properties[i].multiple == 'Y') {
                            document.querySelector("#iblock_id_check_all_label").style.display = 'block';
                        } else {
                            document.querySelector("#iblock_id_value_label").style.display = 'block';
                        }
                    }
                    document.querySelector("#save_wrapper").style.display = 'block';
                }
            }
        }

        function changeSelResultIblock(obj) {
            document.querySelector("#iblock_id_value_label").style.display = 'block';
        }

        function removeOptions(selectbox)
        {
            var i;
            for(i = selectbox.options.length - 1 ; i >= 0 ; i--)
            {
                selectbox.remove(i);
            }
        }
    </script>
<?endif;?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");?>