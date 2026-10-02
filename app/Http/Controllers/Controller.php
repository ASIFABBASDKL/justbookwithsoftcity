<?php

namespace App\Http\Controllers;

use App\Http\Concerns\EnsuresOwnership;

abstract class Controller
{
    use EnsuresOwnership;
}
