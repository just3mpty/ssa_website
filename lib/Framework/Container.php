<?php

namespace CapsuleLib\Framework;

class Container
{
    private array $factories = [];
    private array $instances = [];

    /**
     * Définit une factory de service.
     */
    public function set(string $id, callable $factory): void
    {
        $this->factories[$id] = $factory;
    }

    /**
     * Récupère une instance (créée à la demande, singleton par défaut).
     */
    public function get(string $id)
    {
        if (!isset($this->instances[$id])) {
            if (!isset($this->factories[$id])) {
                throw new \RuntimeException("Service '$id' non défini dans le container.");
            }
            $this->instances[$id] = $this->factories[$id]($this);
        }
        return $this->instances[$id];
    }
}
