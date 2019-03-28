
<section class="container ">
	<aside>
		<button type="button" data-target="leads-change" data-id="byDay" class="btn ">Diario</button>
		<button type="button" data-target="leads-change" data-id="byMonth" class="btn">Mensual</button>
		<button type="button" data-target="leads-change" data-id="total" class="btn active">Acumulado</button>
	</aside>
	<article class="grafico-container">
		<div id="grafico_leads"></div>
	</article>
</section>

<script type="text/javascript" src="<?php echo get_recurso('js'); ?>/widgets/resumen_leads.js"></script>