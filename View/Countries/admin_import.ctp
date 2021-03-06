<div class="page form">
<h2>Schnell-Import von Ländern</h2>

<?php
	if (!empty($this->request->data['Form'])) { ?>
	<h3>Speichern</h3>
	<?php echo $this->Form->create('Country');?>
	<fieldset>
		<legend><?php echo __('Import Countries');?></legend>

		<?php if (!empty($countries)) { ?>
		<div class="">
		<?php echo pre(h($countries)); ?>
		</div>
		<?php } ?>

	<?php

		foreach ($this->request->data['Form'] as $key => $val) {
			echo $this->Form->input('Form.' . $key . '.name', ['value' => $val['name']]);
			echo $this->Form->input('Form.' . $key . '.iso2', ['value' => $val['iso2']]);
			echo $this->Form->input('Form.' . $key . '.iso3', ['value' => $val['iso3']]);
			echo $this->Form->input('Form.' . $key . '.confirm', ['checked' => $val['confirm'], 'type' => 'checkbox', 'label' => 'Einfügen']);

			//echo $this->Form->error('Error.'.$key.'name', 'First Name Required');
			if (!empty($this->request->data['Error'][$key]['name'])) {
				echo h($this->request->data['Error'][$key]['name']) . BR;

			}
			echo BR;
		}
	?>
	</fieldset>
	<?php echo $this->Form->submit(__('Submit')); echo $this->Form->end();?>
<?php } ?>


<h3>Import</h3>


<?php if (true) { ?>

<?php echo $this->Form->create('Country');?>
	<fieldset>
		<legend><?php echo __('Import Countries');?></legend>
	<?php
		echo $this->Form->input('import_separator', ['options' => Country::separators(), 'empty' => [0 => 'Eigenen Separator verwenden']]);
		echo $this->Form->input('import_separator_custom', ['label' => 'Eigener Separator']);

		echo $this->Form->input('import_pattern', []);
		echo $this->Form->input('import_record_separator', ['options' => Country::separators(), 'empty' => [0 => 'Eigenen Separator verwenden']]);
		echo $this->Form->input('import_record_separator_custom', ['label' => 'Eigener Separator']);

		echo 'Für Pattern verwendbar: &nbsp;&nbsp; <b>{TAB}</b>, <b>{SPACE}</b>, <b>benutzerdefinierte Trennzeichen</b>, <b>%*s</b> (Überspringen), <b>%s</b> (ohne Leerzeichen), <b>%[^.]s</b> (mit Leerzeichen)<br>
		Alles, wofür %name zutrifft, verwendet wird, der Rest geht verloren. Was als Separator ausgewählt wurde (zum Trennen der einzelnen Datensätze), kann logischerweise nicht mehr im Pattern verwendet werden (zum Herausfiltern des Namens)!';
		echo BR;
		echo BR;
		echo $this->Form->input('import_content', ['type' => 'textarea', 'rows' => 30]);
	?>
	</fieldset>
<?php echo $this->Form->submit(__('Submit')); echo $this->Form->end();?>

<?php } else { ?>

<?php echo $this->Html->link('Neuer Import', ['action' => 'import']); ?>

<?php } ?>
</div>

<br /><br />

<div class="actions">
	<ul>
		<li><?php echo $this->Html->link(__('List %s', __('Dances')), ['action' => 'index']);?></li>
	</ul>
</div>