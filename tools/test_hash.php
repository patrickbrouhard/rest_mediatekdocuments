<?php

$hash = '$2y$10$QGlaJUnTnG/Xm8rFKeI4C.jT82EMptmCiZxePVi9WqhsXT8Cpkh8q';

var_dump(password_verify("test", $hash));