<?php
// filepath: app/DTOs/Master/LocationDTO.php

namespace App\DTOs\Master;

class LocationDTO
{
    public function __construct(
        public readonly ?string $id,
        public readonly string $name,
        public readonly ?string $address,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
        public readonly int $radius,
        public readonly bool $enforceGeofence,
        public readonly bool $isActive,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            id: $data['id'] ?? null,
            name: $data['name'],
            address: $data['address'] ?? null,
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            radius: (int) ($data['radius'] ?? 100),
            enforceGeofence: (bool) ($data['enforce_geofence'] ?? true),
            isActive: (bool) ($data['is_active'] ?? true),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'address' => $this->address,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'radius' => $this->radius,
            'enforce_geofence' => $this->enforceGeofence,
            'is_active' => $this->isActive,
        ];
    }
}