<?php if ($account) { ?>
<div id="government_block" class="center_block">
    <strong>Government Overview</strong>
    <button type="button" class="exit_center_block btn btn-default btn-sm pull-right">
      <span class="glyphicon glyphicon-remove-sign" aria-hidden="true"></span>
    </button>
    <hr>
    <div class="row">
        <div class="col-md-4 government_card">
            <h3 class="text-center text-primary">Laws</h3>
            <!-- Form -->
            <?php echo form_open('laws_form', array('id' => 'laws_form', 'method' => 'post')); ?>
                <input type="hidden" name="world_key" value="<?php echo $world['id']; ?>">
                <div class="row">
                    <div class="col-md-4">
                        <label for="input_government" class="pull-right">Power Structure: </label>
                    </div>
                    <div class="col-md-8">
                        <select class="form-control" id="input_government" name="input_government" value="<?php echo $account['government']; ?>">
                            <option value="1" title="Difficult Support, Ordinary Corruption" <?php if ((int)$account['government'] === DEMOCRACY_KEY) { echo 'selected'; } ?>>Democracy</option>
                            <!-- Great for economic growth -->
                            <option value="2" title="Moderate Support, Harsh Corruption" <?php if ((int)$account['government'] === OLIGARCHY_KEY) { echo 'selected'; } ?>>Oligarchy</option>
                            <!-- Great for flexibility -->
                            <option value="3" title="Easy Support, Severe Corruption" <?php if ((int)$account['government'] === AUTOCRACY_KEY) { echo 'selected'; } ?>>Autocracy</option>
                            <!-- Great for early expansion and war -->
                        </select>
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-4">
                        <label for="input_tax_rate" class="pull-right">Tax Rate: (%)</label>
                    </div>
                    <div class="col-md-8">
                        <input type="number" min="0" max="100" required class="form-control" id="tax_rate" name="input_tax_rate" value="<?php echo $account['tax_rate']; ?>">
                    </div>
                </div>
                <br>
                <div class="row">
                    <div class="col-md-4">
                        <label for="input_government" class="pull-right">Economic Ideology: </label>
                    </div>
                    <div class="col-md-8">
                        <div class="col-md-3">
                            <label for="input_ideology" class="pull-right text-success">Free Market</label>
                        </div>
                        <div class="col-md-3">
                            <input type="radio" class="form-control" id="free_market" name="input_ideology" value="<?php echo FREE_MARKET_KEY; ?>" <?php echo $account['ideology'] == FREE_MARKET_KEY ? 'checked' : ''; ?> >
                        </div>
                        <div class="col-md-3">
                            <label for="input_ideology" class="pull-right text-danger">Marxist Socialism</label>
                        </div>
                        <!-- Socialism eliminates all profit, units cost support instead of money -->
                        <div class="col-md-3">
                            <input type="radio" class="form-control" id="socialism" name="input_ideology" value="<?php echo SOCIALISM_KEY; ?>" <?php echo $account['ideology'] == SOCIALISM_KEY ? 'checked' : ''; ?> >
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-8 col-md-push-4">
                        <hr>
                        <div id="pass_new_laws_button" class="btn btn-action form-control text-is-bold">Sign Into Law</div>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-md-4 government_card">
            <h3 class="text-center text-primary">Budget</h3>
            <span class="budget_info_item_parent">
                <strong class="budget_info_item_label">GDP: </strong>
                <strong class="budget_info_value text-action">
                    $<span id="foobar">5,400</span> Million
                </strong><br>
            </span>
            <span class="budget_info_item_parent">
                <strong class="budget_info_item_label">Tax Income: </strong>
                <strong class="budget_info_value text-success">
                    $<span id="foobar">800</span> Million
                </strong><br>
            </span>
            <span class="budget_info_item_parent">
                <strong class="budget_info_item_label">Corruption: </strong>
                <strong class="budget_info_value text-red">
                    -$<span id="foobar">50</span> Million
                </strong><br>
            </span>
            <span class="budget_info_item_parent">
                <strong class="budget_info_item_label">Federal Administrative: </strong>
                <strong class="budget_info_value text-red">
                    -$<span id="foobar">50</span> Million
                </strong><br>
            </span>
            <span class="budget_info_item_parent">
                <strong class="budget_info_item_label">Military Bases: </strong>
                <strong class="budget_info_value text-red">
                    -$<span id="foobar">0</span> Million
                </strong><br>
            </span>
            <span class="budget_info_item_parent">
                <strong class="budget_info_item_label">University Education: </strong>
                <strong class="budget_info_value text-red">
                    -$<span id="foobar">30</span> Million
                </strong><br>
            </span>
            <span class="budget_info_item_parent">
                <strong class="budget_info_item_label">Subsidized Healthcare: </strong>
                <strong class="budget_info_value text-red">
                    -$<span id="foobar">0</span> Million
                </strong><br>
            </span>
            <!-- <span class="budget_info_item_parent">
                <strong class="budget_info_item_label">Socialism: </strong>
                <strong class="budget_info_value text-red">
                    -$<span id="foobar">670</span> Million
                </strong><br>
            </span> -->
            <span class="budget_info_item_parent">
                <strong class="budget_info_item_label">Hourly Earnings: </strong>
                <strong class="budget_info_value text-success">
                    $<span id="foobar">670</span> Million
                </strong><br>
            </span>
        </div>
        <div class="col-md-4 government_card">
            <!-- <h3 class="text-center text-primary">Supplies</h3> -->
            <div class="government_supplies_parent row">
            <?php $current_category_id = 0; ?>
            <?php $category_counter = 0; ?>
            <?php foreach ($this->supplies as $key => $supply) { ?>
                <?php
                if ($category_counter % 2 == 0) { ?>
                    <div class="col-xs-12"></div>
                <?php }
                if ($current_category_id !== $supply['category_id']) {
                    $category_counter++;
                    $current_category_id = $supply['category_id'];
                    ?>
                    <div class="col-md-6">
                        <h3 class="text-purple"><?php echo $this->supplies_category_labels[$supply['category_id']]; ?></h3>
                    <?php 
                } ?>

                <div class="government_supply parent">
                    <label class="text-primary">
                        <?php echo $supply['label']; ?>
                        <?php if ($supply['can_sell']) { ?>
                        <span class="text-success">
                            ($<?php echo mt_rand(1, 6); ?>M)
                        </span>
                        <?php } ?>
                    </label>
                    <span class="pull-right">
                        <?php if ($supply['can_sell']) { ?>
                        <a href="" class="sell_resource">
                            (Sell)
                        </a>
                        <?php } ?>
                        <span id="government_supply_<?php echo $supply['slug']; ?>"></span>
                        <?php echo $supply['suffix']; ?>
                    </span>
                    <br>
                </div>

                <?php if (!isset($this->supplies[$key + 1]) || $current_category_id !== $this->supplies[$key + 1]['category_id']) {
                    ?>
                    </div>
                    <?php 
                    }
                ?>
            <?php } ?>
            </div>
        </div>
    </div>
</div>
<?php } ?>