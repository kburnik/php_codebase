<?php
# Entry point for {target}.
array_shift($argv);
exit(intval(\{namespace}\{class}::main($argv)));
