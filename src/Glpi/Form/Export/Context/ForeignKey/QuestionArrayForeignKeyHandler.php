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

namespace Glpi\Form\Export\Context\ForeignKey;

use Glpi\Form\Export\Context\DatabaseMapper;
use Glpi\Form\Export\Specification\DataRequirementSpecification;
use Glpi\Form\Question;

/**
 * Handle an array of foreign keys for Question items.
 */
final class QuestionArrayForeignKeyHandler implements JsonConfigForeignKeyHandlerInterface
{
    public function __construct(
        private string $key,
        private array $ignored_values = [],
    ) {
    }

    public function getDataRequirements(array $serialized_data): array
    {
        if (!$this->keyExistInSerializedData($serialized_data)) {
            return [];
        }

        $requirements = [];
        $foreign_keys = $serialized_data[$this->key];

        // Create a data requirement for each foreign key
        foreach ($foreign_keys as $foreign_key) {
            if ($this->isIgnoredValue($foreign_key)) {
                continue;
            }

            // Load item
            $item = new Question();
            if ($item->getFromDB($foreign_key)) {
                $requirements[] = new DataRequirementSpecification(
                    Question::class,
                    $item->getUniqueIDInForm(),
                );
            }
        }

        return $requirements;
    }

    public function replaceForeignKeysByNames(array $serialized_data): array
    {
        if (!$this->keyExistInSerializedData($serialized_data)) {
            // Key can be exist but defined as null, so we need to unset it
            unset($serialized_data[$this->key]);
            return $serialized_data;
        }

        $data_with_names = [];
        $foreign_keys = $serialized_data[$this->key];

        // Replace each foreign key by the name of the item it references
        foreach ($foreign_keys as $foreign_key) {
            if ($this->isIgnoredValue($foreign_key)) {
                // Value isn't a fkey, keep it as it is
                $data_with_names[] = $foreign_key;
                continue;
            }

            // Load item
            $item = new Question();
            if ($item->getFromDB($foreign_key)) {
                $data_with_names[] = $item->getUniqueIDInForm();
            }
        }

        if (!empty($data_with_names)) {
            $serialized_data[$this->key] = $data_with_names;
        } else {
            unset($serialized_data[$this->key]);
        }

        return $serialized_data;
    }

    public function replaceNamesByForeignKeys(
        array $serialized_data,
        DatabaseMapper $mapper,
    ): array {
        if (!$this->keyExistInSerializedData($serialized_data)) {
            return $serialized_data;
        }

        $data_with_fkeys = [];
        $names = $serialized_data[$this->key];

        // Replace names by its database id
        foreach ($names as $name) {
            if ($this->isIgnoredValue($name)) {
                // Value isn't a name, keep it as it is
                $data_with_fkeys[] = $name;
                continue;
            }

            $data_with_fkeys[] = $mapper->getItemId(
                Question::class,
                $name
            );
        }

        $serialized_data[$this->key] = $data_with_fkeys;
        return $serialized_data;
    }

    private function keyExistInSerializedData(array $serialized_data): bool
    {
        return isset($serialized_data[$this->key]);
    }

    private function isIgnoredValue(mixed $value): bool
    {
        return in_array($value, $this->ignored_values);
    }
}
