<?php

namespace Arrtxp\SqlDriver;

enum JoinType: string
{
    case INNER = 'INNER';
    case LEFT = 'LEFT';
    case RIGHT = 'RIGHT';
    case CROSS = 'CROSS';
}