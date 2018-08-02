<?php

use App\Admin\Extensions\WangEditor;
use Encore\Admin\Form;

Form::extend('editor', WangEditor::class);
