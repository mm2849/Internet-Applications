<?php
//mm2849
//07/10/2024
function reset_session()
{
    session_unset();
    session_destroy();
    session_start();
}