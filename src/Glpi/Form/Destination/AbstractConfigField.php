<?php

/**
 * ---------------------------------------------------------------------
 *
 * GLPI - Gestionnaire Libre de Parc Informatique
 *
 * http://glpi-project.org
 *
 * @copyright 2015-2024 Teclib' and contributors.
 * @copyright 2003-2014 by the INDEPNET Development Team.
 * @licence   https://www.gnu.org/licenses/gpl-3.0.html
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * ---------------------------------------------------------------------
 */

namespace Glpi\Form\Destination;

use Glpi\DBAL\JsonFieldInterface;
use Glpi\Form\Form;
use LogicException;
use Override;
use Toolbox;

abstract class AbstractConfigField implements ConfigFieldInterface
{
    #[Override]
    public function getKey(): string
    {
        return Toolbox::slugify(static::class);
    }

    #[Override]
    public function supportAutoConfiguration(): bool
    {
        return false;
    }

    #[Override]
    public function getAutoGeneratedConfig(Form $form): ?JsonFieldInterface
    {
        return null;
    }

    public function getConfig(Form $form, array $config): JsonFieldInterface
    {
        if ($this->supportAutoConfiguration() && $this->isAutoConfigurated($config)) {
            $config = $this->getAutoGeneratedConfig($form);
            if ($config === null) {
                throw new LogicException(
                    "getAutoGeneratedConfig can't return null if supportAutoConfiguration is true"
                );
            }

            return $config;
        }

        // Try to load config if defined
        $config = $config[$this->getKey()] ?? null;
        if ($config === null) {
            return $this->getDefaultConfig($form);
        }

        $config_class = $this->getConfigClass();
        return $config_class::jsonDeserialize($config);
    }

    public function isAutoConfigurated(array $config): bool
    {
        return $config[$this->getAutoConfigKey()] ?? true;
    }

    public function getAutoConfigKey(): string
    {
        return $this->getKey() . '_auto';
    }

    #[Override]
    public function prepareInput(array $input): array
    {
        return $input;
    }
}
