<?php
# Generated test runner for target {target}.

$command = new PHPUnit_TextUI_Command;

$srcs = {srcs};
foreach ($srcs as $src) {
  $command->run(array('phpunit', $src), true);
}
