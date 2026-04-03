<?php

enum TypeDocument
{
    case LIVRE;
    case REVUE;
    case DVD;

    // index artificiel
    public function index(): int
    {
        return match($this) {
            self::LIVRE => 0,
            self::REVUE => 1,
            self::DVD   => 2,
        };
    }

    public function table(): string
    {
        return match($this) {
            self::LIVRE => "livre",
            self::REVUE => "revue",
            self::DVD   => "dvd",
        };
    }
}
