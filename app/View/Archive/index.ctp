<div class="entry">
	<h1 class="name c1">Archive</h1>
	<p class="content c2">
		<ol style="list-style-type: none;">
		<?php foreach($entries as $e){?>
			<li>
				<a class="c2 bold" href="<?=$e['Entry']['name']?>"><?=$e['Entry']['name']?></a>
			</li>
		<?php }	?>
		</ol>
	</p>
</div>