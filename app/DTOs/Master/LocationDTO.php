<?php
// filepath: app/DTOs/Master/LocationDTO.php

namespace App\DTOs\Master;

class LocationDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly ?string $address,
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly int $radius,
        public readonly bool $enforce_geofence,
        public readonly bool $is_active,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            address: $data['address'] ?? null,
            latitude: $data['latitude'],
            longitude: $data['longitude'],
            radius: $data['radius'] ?? 100,
            enforce_geofence: $data['enforce_geofence'] ?? true,
            is_active: $data['is_active'] ?? true,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius' => $this->radius,
            'enforce_geofence' => $this->enforce_geofence,
            'is_active' => $this->is_active,
        ], fn($value) => $value !== null);
    }
}