<?php
namespace V3\Feature\Register;

enum RegisterError
{
    case UsernameTaken;
    case UsernameMalformed;
    case EmailTaken;
    case PasswordNotSecure;
    case PasswordNotRepeated;
    case TermsNotAccepted;
}
