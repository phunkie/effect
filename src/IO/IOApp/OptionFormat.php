<?php

namespace Phunkie\Effect\IO\IOApp;

enum OptionFormat
{
    case Optional;
    case Required;
    case Negatable;
    case ArrayValues;
    case NoInput;
}
