<?php

namespace Wizard\App;

interface MiddlewareInterface
{
    
    public function handle(Request $request);
    
}