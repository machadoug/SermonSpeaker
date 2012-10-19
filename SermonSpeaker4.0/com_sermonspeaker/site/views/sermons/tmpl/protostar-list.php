<?php
defined('_JEXEC') or die;

JHTML::addIncludePath(JPATH_COMPONENT.'/helpers');

JHtml::_('behavior.tooltip');
JHtml::_('behavior.modal');
JHtml::_('bootstrap.tooltip');

$user		= JFactory::getUser();
$canEdit	= $user->authorise('core.edit', 'com_sermonspeaker');
$canEditOwn	= $user->authorise('core.edit.own', 'com_sermonspeaker');
$listOrder	= $this->state->get('list.ordering');
$listDirn	= $this->state->get('list.direction');
$limit 		= (int)$this->params->get('limit', '');
$player		= new SermonspeakerHelperPlayer($this->items);
?>
<div class="category-list<?php echo $this->pageclass_sfx;?> ss-sermons-container<?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	<?php endif;
	if ($this->params->get('show_category_title', 1) or $this->params->get('page_subheading')) : ?>
		<h2>
			<?php echo $this->escape($this->params->get('page_subheading'));
			if ($this->params->get('show_category_title')) : ?>
				<span class="subheading-category"><?php echo $this->category->title;?></span>
			<?php endif; ?>
		</h2>
	<?php endif;
	if ($this->params->get('show_description', 1) or $this->params->get('show_description_image', 1)) : ?>
		<div class="category-desc">
			<?php if ($this->params->get('show_description_image') and $this->category->getParams()->get('image')) : ?>
				<img src="<?php echo $this->category->getParams()->get('image'); ?>"/>
			<?php endif;
			if ($this->params->get('show_description') and $this->category->description) :
				echo JHtml::_('content.prepare', $this->category->description, '', 'com_sermonspeaker.category');
			endif; ?>
			<div class="clr"></div>
		</div>
	<?php endif;
	if (in_array('sermons:player', $this->columns) and count($this->items)) :
		JHTML::stylesheet('com_sermonspeaker/player.css', '', true); ?>
		<div class="ss-sermons-player span10 offset1">
			<hr class="ss-sermons-player" />
			<?php if ($player->player != 'PixelOut') : ?>
				<div id="playing">
					<img id="playing-pic" class="picture" src="" />
					<span id="playing-duration" class="duration"></span>
					<div class="text">
						<span id="playing-title" class="title"></span>
						<span id="playing-desc" class="desc"></span>
					</div>
					<span id="playing-error" class="error"></span>
				</div>
			<?php endif;
			echo $player->mspace;
			echo $player->script;
			?>
			<hr class="ss-sermons-player" />
			<?php if ($player->toggle) : ?>
				<div>
					<img class="pointer" src="media/com_sermonspeaker/images/Video.png" onclick="Video()" alt="Video" title="<?php echo JText::_('COM_SERMONSPEAKER_SWITCH_VIDEO'); ?>" />
					<img class="pointer" src="media/com_sermonspeaker/images/Sound.png" onclick="Audio()" alt="Audio" title="<?php echo JText::_('COM_SERMONSPEAKER_SWITCH_AUDIO'); ?>" />
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<div class="cat-items">
		<form action="<?php echo htmlspecialchars(JUri::getInstance()->toString()); ?>" method="post" id="adminForm" name="adminForm" class="form-inline">
			<?php if ($this->params->get('filter_field') or $this->params->get('show_pagination_limit')) : ?>
				<div class="filters btn-toolbar">
					<?php if ($this->params->get('filter_field')) :?>
						<div class="btn-group">
							<label class="filter-search-lbl element-invisible" for="filter-search">
								<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
								<?php echo JText::_('JGLOBAL_FILTER_LABEL').'&#160;'; ?>
							</label>
							<input type="text" name="filter-search" id="filter-search" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" class="input-medium" onchange="document.adminForm.submit();" title="<?php echo JText::_('COM_SERMONSPEAKER_FILTER_SEARCH_DESC'); ?>" placeholder="<?php echo JText::_('COM_SERMONSPEAKER_FILTER_SEARCH_DESC'); ?>" />
						</div>
						<div class="btn-group filter-select">
							<select name="book" id="filter_books" class="input-medium" onchange="this.form.submit()">
								<?php echo JHtml::_('select.options', $this->books, 'value', 'text', $this->state->get('scripture.book'), true);?>
							</select>
							<select name="month" id="filter_months" class="input-medium" onchange="this.form.submit()">
								<option value="0"><?php echo JText::_('COM_SERMONSPEAKER_SELECT_MONTH'); ?></option>
								
								<?php echo JHtml::_('select.options', $this->months, 'value', 'text', $this->state->get('date.month'), true);?>
							</select>
							<select name="year" id="filter_years" class="input-medium" onchange="this.form.submit()">
								<option value="0"><?php echo JText::_('COM_SERMONSPEAKER_SELECT_YEAR'); ?></option>
								<?php echo JHtml::_('select.options', $this->years, 'year', 'year', $this->state->get('date.year'), true);?>
							</select>
						</div>
					<?php endif;
					if ($this->params->get('show_pagination_limit')) : ?>
						<div class="btn-group pull-right">
							<label class="element-invisible">
								<?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>
							</label>
							<?php echo $this->pagination->getLimitBox(); ?>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<div class="clearfix"></div>
			<?php if (!count($this->items)) : ?>
				<div class="no_entries alert alert-error"><?php echo JText::sprintf('COM_SERMONSPEAKER_NO_ENTRIES', JText::_('COM_SERMONSPEAKER_SERMONS')); ?></div>
			<?php else : ?>
				<ul class="category list-striped list-condensed">
					<?php foreach($this->items as $i => $item) :
						$sep = 0; ?>
						<li id="sermon<?php echo $i; ?>" class="<?php echo ($item->state) ? '': 'system-unpublished '; ?>cat-list-row<?php echo $i % 2; ?>">
							<?php if (in_array('sermons:hits', $this->columns)) : ?>
								<span class="ss-hits badge badge-info pull-right">
									<?php echo JText::sprintf('JGLOBAL_HITS_COUNT', $item->hits); ?>
								</span>
							<?php endif;
							if ($canEdit or ($canEditOwn and ($user->id == $item->created_by))) : ?>
								<span class="list-edit pull-left width-50">
									<?php echo JHtml::_('icon.edit', $item, $this->params, array('type' => 'sermon')); ?>
								</span>
							<?php endif; ?>
							<strong class="ss-title">
								<?php echo SermonspeakerHelperSermonspeaker::insertSermonTitle($i, $item, $player); ?>
							</strong>
							<?php if (!$item->state) : ?>
								<span class="label label-warning"><?php echo JText::_('JUNPUBLISHED'); ?></span>
							<?php endif; ?>
							<br />
							<?php if (in_array('sermons:speaker', $this->columns) and $item->name) :
								$sep = 1; ?>
								<small class="ss-speaker">
									<?php echo JText::_('COM_SERMONSPEAKER_SPEAKER'); ?>: 
									<?php if ($item->speaker_state):
										echo SermonspeakerHelperSermonSpeaker::SpeakerTooltip($item->speaker_slug, $item->pic, $item->name);
									else :
										echo $item->name;
									endif; ?>
								</small>
							<?php endif;
							if (in_array('sermons:series', $this->columns) and $item->series_title) :
								if ($sep) : ?>
									|
								<?php endif;
								$sep = 1; ?>
								<small class="ss-series">
									<?php echo JText::_('COM_SERMONSPEAKER_SERIE'); ?>: 
									<?php if ($item->series_state): ?>
										<a href="<?php echo JRoute::_(SermonspeakerHelperRoute::getSerieRoute($item->series_slug)); ?>">
											<?php echo $item->series_title; ?>
										</a>
									<?php else :
										echo $item->series_title;
									endif; ?>
								</small>
							<?php endif;
							if (in_array('sermons:length', $this->columns) and $item->sermon_time != '00:00:00') :
								if ($sep) : ?>
									|
								<?php endif;
								$sep = 1; ?>
								<small class="ss-length">
									<?php echo JText::_('COM_SERMONSPEAKER_FIELD_LENGTH_LABEL'); ?>: 
									<?php echo SermonspeakerHelperSermonspeaker::insertTime($item->sermon_time); ?>
								</small>
							<?php endif;
							if (in_array('sermons:scripture', $this->columns) and $item->scripture) :
								if ($sep) : ?>
									|
								<?php endif;
								$sep = 1; ?>
								<small class="ss-scripture">
									<?php echo JText::_('COM_SERMONSPEAKER_FIELD_SCRIPTURE_LABEL'); ?>: 
									<?php $scriptures = SermonspeakerHelperSermonspeaker::insertScriptures($item->scripture, '; ');
									echo JHTML::_('content.prepare', $scriptures, '', 'com_sermonspeaker.scripture'); ?>
								</small>
							<?php endif;
							if (in_array('sermons:date', $this->columns) and ($item->sermon_date != '0000-00-00 00:00:00')) : ?>
								<span class="ss-date small pull-right">
									<?php echo JHTML::date($item->sermon_date, JText::_($this->params->get('date_format')), true); ?>
								</span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif;
			if ($user->authorise('core.create', 'com_sermonspeaker')) :
				echo JHtml::_('icon.create', $this->category, $this->params);
			endif;
			if ($this->params->get('show_pagination') and ($this->pagination->get('pages.total') > 1)) : ?>
				<div class="pagination">
					<?php if ($this->params->get('show_pagination_results', 1)) : ?>
						<p class="counter pull-right">
							<?php echo $this->pagination->getPagesCounter(); ?>
						</p>
					<?php endif;
					echo $this->pagination->getPagesLinks(); ?>
				</div>
			<?php endif; ?>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<input type="hidden" name="limitstart" value="" />
		</form>
	</div>
	<?php if (!empty($this->children[$this->category->id]) and $this->maxLevel != 0) : ?>
		<div class="cat-children">
			<h3><?php echo JTEXT::_('JGLOBAL_SUBCATEGORIES'); ?></h3>
			<?php echo $this->loadTemplate('children30'); ?>
		</div>
	<?php endif; ?>
</div>