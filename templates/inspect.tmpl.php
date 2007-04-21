<?php 
    $this->page_id = 'inspect';
    $this->has_sidebar = FALSE; 

    $pipeline = $this->pipeline;
?>
<?php include $this->template('_header.tmpl.php'); ?>
    
    <h2>Inspect Pipeline: <?php $this->eprint($pipeline['title']) ?></h2>

    <p><?php $this->eprint($pipeline['description']) ?></p>

    <form method="GET" action="<?php echo $this->BASE_URL ?>">
        <input type="hidden" name="pipeline" value="<?php $this->eprint($this->pipeline_name) ?>" />

        <ul class="fields">
            <?php if (!$pipeline['parameters']): ?>

                <li>
                    This pipeline requires no parameters.
                </li>

                <li>
                    <input type="submit" class="submit" name="run" value="Run Pipeline" />
                    &mdash;
                    <a class="viewsource" href="<?php $this->eprint($this->BASE_URL.'/pipelines/'.$this->pipeline_name) ?>">View Pipeline Source</a>
                </li>

            <?php else: ?>
                
                <?php foreach ($pipeline['parameters'] as $name=>$opts): ?>
                    <li>
                        <label for="<?php $this->eprint($name) ?>"><?php $this->eprint($opts['label']) ?></label>
                        <?php 
                            switch ($opts['type']) {
                                case 'string': 
                                default: ?>
                                    <input type="text" class="text" name="<?php $this->eprint($name) ?>" 
                                        value="<?php $this->eprint(isset($opts['default']) ? $opts['default'] : '' ) ?>" />
                                <?php
                            }
                        ?>
                            
                    </li>
                <?php endforeach ?>

                <li>
                    <label>&nbsp;</label>
                    <input type="submit" class="submit" name="run" value="Run Pipeline" />
                    &mdash;
                    <a class="viewsource" href="<?php $this->eprint($this->BASE_URL.'/pipelines/'.$this->pipeline_name) ?>">View Pipeline Source</a>
                </li>

            <?php endif ?>

        </ul>


    </form>

    <h3>Other Local Pipelines</h3>
    <ul class="pipelines">
        <?php foreach ($this->pipelines as $name=>$meta): ?>
            <li>
                <h4><a href="<?php echo $this->BASE_URL ?>/inspect/<?php $this->eprint($name) ?>"><?php $this->eprint($meta['title']) ?></a></h4>
                <p><?php $this->eprint($meta['description']) ?></p>
            </li>
        <?php endforeach ?>
    </ul>

<?php include $this->template('_footer.tmpl.php'); ?>
