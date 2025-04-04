<?php

class Product
{
    private int $id;
    private string $name;
    private string $image;
    private string $category;
    private float $price;
    private string $info;

    public function __construct(int $id, string $name, string $image, string $category, float $price, string $info)
    {
        $this->id = $id;
        $this->name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $this->image = htmlspecialchars($image, ENT_QUOTES, 'UTF-8');
        $this->category = htmlspecialchars($category, ENT_QUOTES, 'UTF-8');
        $this->price = (float) $price;
        $this->info = htmlspecialchars($info, ENT_QUOTES, 'UTF-8');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getImage(): string
    {
        return $this->image;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getInfo(): string
    {
        return $this->info;
    }
}
?>
