
<div class="page form">
<?php echo $this->Form->create('Currency');?>
	<fieldset>
		<legend><?php echo __('Add %s', __('Currency'));?></legend>

	<?php
		echo $this->Form->input('name');
		echo $this->Form->input('code', ['datalist' => $currencies]);
		echo $this->Form->input('symbol_left');
		echo $this->Form->input('symbol_right');
		echo $this->Form->input('decimal_places');
		echo $this->Form->input('value');
	?>
	</fieldset>
<?php echo $this->Form->submit(__('Submit')); echo $this->Form->end();?>
</div>

<br />

Wird nur der Code angegeben, wird versucht, den Name automatisch dazu zu finden.
Das selbe gilt für den aktuellen Wechselkurs.

<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('List %s', __('Currencies')), ['action' => 'index']);?></li>
	</ul>
</div>