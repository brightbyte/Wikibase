<?php

namespace Wikibase;

/**
 * Contains methods for interaction with an entity store.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @since 0.3
 *
 * @file
 * @ingroup WikibaseLib
 *
 * @licence GNU GPL v2+
 * @author Jeroen De Dauw < jeroendedauw@gmail.com >
 * @author Daniel Kinzler
 */
interface EntityLookup {

	/**
	 * Returns the entity with the provided id or null is there is no such
	 * entity. If a $revision is given, the requested revision of the entity is loaded.
	 * The the revision does not belong to the given entity, null is returned.
	 *
	 * @since 0.3
	 *
	 * @param EntityID $entityId
	 * @param int|bool $revision
	 *
	 * @return Entity|null
	 */
	public function getEntity( EntityID $entityId, $revision = false );

	/**
	 * Fetches the entities with provided ids and returns them.
	 * The result array contains the prefixed entity ids as keys.
	 * The values are either an Entity or null, if there is no entity with the associated id.
	 *
	 * The revisions can be specified as an array holding an integer element for each
	 * id in the $entityIds array or false for latest. If all should be latest, false
	 * can be provided instead of an array.
	 *
	 * @since 0.4
	 *
	 * @param EntityID[] $entityIds
	 * @param array|bool $revision
	 *
	 * @return Entity|null[]
	 */
	public function getEntities( array $entityIds, $revision = false );

}