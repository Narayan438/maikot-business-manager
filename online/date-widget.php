<?php
function mk_date_widget(string $name,string $value='',string $mode='BS'):string{
  $n=htmlspecialchars($name,ENT_QUOTES,'UTF-8');$v=htmlspecialchars($value,ENT_QUOTES,'UTF-8');$m=strtoupper($mode)==='AD'?'AD':'BS';
  return '<div class="mk-dual-date" data-mode="'.$m.'" data-value="'.$v.'"><input type="hidden" data-dual-hidden name="'.$n.'" value="'.$v.'"><span class="mk-switch"><button type="button" data-mode="BS">BS</button><button type="button" data-mode="AD">AD</button></span><input class="mk-bs" type="text" inputmode="numeric" placeholder="2083-06-02"><input class="mk-ad" type="date"></div><div class="mk-date-note">BS ⇄ AD automatically synchronized</div>';
}
?>