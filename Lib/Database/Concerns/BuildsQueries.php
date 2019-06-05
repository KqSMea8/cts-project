<?php

trait Database_Concerns_BuildsQueries
{
    public function first($columns = ['*'])
    {
        return $this->take(1)->get($columns)->first();
    }
}