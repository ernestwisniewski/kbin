<?php

declare(strict_types=1);

namespace App\Markdown\CommonMark;
 
enum MentionType {
    case Magazine;  
    case RemoteMagazine;
    case RemoteUser;
    case User;  
}