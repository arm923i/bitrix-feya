<?
if ($_REQUEST["j"] == date("j") && $_REQUEST["n"] + 1 == date("n") && $_REQUEST["Y"] == date("Y")) {
    $m = intval(date("i")) + 30;
    if ($m > 59) {
        $h = intval(date("H")) + 1;
        $m -= 60;
    }
    if ($h > 23) {
        $h = $h - 24;
    }
    else $h = intval(date("H"));
    echo make_time($h, $m);
    for ($j = ceil(($m + 1) / 30) * 30; $j < 60; $j += 30) echo make_time($h, $j);
}
for ($i = $h ? $h + 1 : 0; $i < 24; $i++) for ($j = 0; $j < 60; $j += 30) echo make_time($i, $j);
function make_time($h, $m) {
    $time = ($h < 10 ? "0". $h : $h) . ":" . ($m < 10 ? "0" . $m : $m);
    $s = '';
    if($time == $_REQUEST['time'])
        $s = ' selected';
    return "<option value=\"" . $time . "\"".$s.">" . $time . "</option>";
}
?>