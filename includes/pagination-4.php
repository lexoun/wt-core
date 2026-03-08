<?php if($pocet_prispevku>4){ ?>
        
<?php
$pred=$od-1;
$po=$od+1;
$po_s=$od*4;
if($od>1){ ?>
 <li style="margin-top: -4px;">
		
		<div class="cbp_tmicon">
			<i class="entypo-left"></i>
		</div>
		
		<div class="cbp_tmlabel empty">
			<span><a href="<?= $home ?>/admin/<?= $pagipage ?>?od=<?= $pred ?>" style="text-decoration: underline;">Novější nákupy</a></span>
		</div>
	</li> 

<?php }
if($po_s<$pocet_prispevku){ ?>

 <li style="margin-top: -4px;">
		
		<div class="cbp_tmicon">
			<i class="entypo-right"></i>
		</div>
		
		<div class="cbp_tmlabel empty">
			<span><a href="<?= $home ?>/admin/<?= $pagipage ?>?od=<?= $po ?>" style="text-decoration: underline;">Starší nákupy</a></span>
		</div>
	</li>    
<?php

} 

?>
 
<?php } ?>

</ul> 
