<?php
/**
 * Tina4 - This is not a 4ramework.
 * Copy-right 2007 - current Tina4
 * License: MIT https://opensource.org/licenses/MIT
 */

namespace Tina4;

/**
 * FirebirdMetaData retrieves the Firebird metadata from the database
 */
class FirebirdMetaData extends DataConnection implements DataBaseMetaData
{
    /**
     * Get all the tables for the database
     * @return array
     */
    final public function getTables() : array
    {
        $sqlTables = 'SELECT DISTINCT trim(rdb$relation_name) as table_name
                        FROM rdb$relation_fields
                       WHERE rdb$system_flag=0
                         AND rdb$view_context is null';

        $tables = $this->getConnection()->fetch($sqlTables, 1000, 0);

        if (!empty($tables)) {
            return $tables->asObject();
        }

        return [];
    }

    /**
     * Gets the information for a specific table
     * @param string $tableName
     * @return array
     */
    final public function getTableInformation(string $tableName) : array
    {
        $tableInformation = [];
        $sqlInfo = 'SELECT r.RDB$FIELD_NAME AS field_name,
                           r.RDB$DESCRIPTION AS field_description,
                           CAST(r.RDB$DEFAULT_VALUE AS blob sub_type text)  AS field_default_value,
                           r.RDB$NULL_FLAG AS field_not_null_constraint,
                           f.RDB$FIELD_LENGTH AS field_length,
                           f.RDB$FIELD_PRECISION AS field_precision,
                           f.RDB$FIELD_SCALE AS field_scale,
                           CASE f.RDB$FIELD_TYPE
                              WHEN 261 THEN \'BLOB\'
                              WHEN 14 THEN \'CHAR\'
                              WHEN 40 THEN \'CSTRING\'
                              WHEN 11 THEN \'D_FLOAT\'
                              WHEN 27 THEN \'DOUBLE\'
                              WHEN 10 THEN \'FLOAT\'
                              WHEN 16 THEN \'INT64\'
                              WHEN 8 THEN \'INTEGER\'
                              WHEN 9 THEN \'QUAD\'
                              WHEN 7 THEN \'SMALLINT\'
                              WHEN 
                                  12 THEN \'DATE\'
                              WHEN 13 THEN \'TIME\'
                              WHEN 35 THEN \'TIMESTAMP\'
                              WHEN 37 THEN \'VARCHAR\'
                              ELSE \'UNKNOWN\'
                            END AS field_type,
                            f.RDB$FIELD_SUB_TYPE AS field_subtype,
                            coll.RDB$COLLATION_NAME AS field_collation,
                            cset.RDB$CHARACTER_SET_NAME AS field_charset
                      FROM RDB$RELATION_FIELDS r
                 LEFT JOIN RDB$FIELDS f ON r.RDB$FIELD_SOURCE = f.RDB$FIELD_NAME
                 LEFT JOIN RDB$COLLATIONS coll ON r.RDB$COLLATION_ID = coll.RDB$COLLATION_ID
                       AND f.RDB$CHARACTER_SET_ID = coll.RDB$CHARACTER_SET_ID
                 LEFT JOIN RDB$CHARACTER_SETS cset ON f.RDB$CHARACTER_SET_ID = cset.RDB$CHARACTER_SET_ID
                     WHERE r.RDB$RELATION_NAME = \'' . $tableName . '\'
                  ORDER BY r.RDB$FIELD_POSITION';

        $columns = $this->getConnection()->fetch($sqlInfo, 1000, 0)->AsObject();

        $primaryKeys = $this->getPrimaryKeys($tableName);
        $primaryKeyLookup = [];
        foreach ($primaryKeys as $primaryKey) {
            $primaryKeyLookup[$primaryKey->fieldName] = true;
        }

        $foreignKeys = $this->getForeignKeys($tableName);
        $foreignKeyLookup = [];
        foreach ($foreignKeys as $foreignKey) {
            $foreignKeyLookup[$foreignKey->fieldName] = true;
        }

        foreach ($columns as $columnIndex => $columnData) {
            $fieldData = new \Tina4\DataField(
                $columnIndex,
                trim($columnData->fieldName),
                trim($columnData->fieldName),
                trim($columnData->fieldType),
                (int)trim($columnData->fieldPrecision),
                (int)trim($columnData->fieldScale)*-1
            );

            $fieldData->description = trim($columnData->fieldDescription);

            $fieldData->isNotNull = false;
            if ($columnData->fieldNotNullConstraint === 1) {
                $fieldData->isNotNull = true;
            }

            $fieldData->isPrimaryKey = false;
            if (isset($primaryKeyLookup[$fieldData->fieldName])) {
                $fieldData->isPrimaryKey = true;
            }

            $fieldData->isForeignKey = false;
            if (isset($foreignKeyLookup[$fieldData->fieldName])) {
                $fieldData->isForeignKey = true;
            }

            $fieldData->defaultValue = (new FirebirdBlrDecoder())->decodeBlr(trim($columnData->fieldDefaultValue));
            $tableInformation[] = $fieldData;
        }

        return $tableInformation;
    }

    /**
     * Gets the complete database metadata
     * @return array
     */
    final public function getDatabaseMetaData(): array
    {
        $database = [];
        $tables = $this->getTables();

        foreach ($tables as $record) {
            $tableInfo = $this->getTableInformation($record->tableName);

            $database[strtolower($record->tableName)] = $tableInfo;
        }

        return $database;
    }

    /**
     * Gets the primary keys for a table
     * @param string $tableName
     * @return array
     */
    final public function getPrimaryKeys(string $tableName): array
    {
        return $this->getConnection()->fetch(
            'SELECT rc.RDB$CONSTRAINT_NAME,
                          trim(s.RDB$FIELD_NAME) AS field_name,
                          rc.RDB$CONSTRAINT_TYPE AS constraint_type
                     FROM RDB$INDEX_SEGMENTS s
                LEFT JOIN RDB$INDICES i ON i.RDB$INDEX_NAME = s.RDB$INDEX_NAME
                LEFT JOIN RDB$RELATION_CONSTRAINTS rc ON rc.RDB$INDEX_NAME = s.RDB$INDEX_NAME
                LEFT JOIN RDB$REF_CONSTRAINTS refc ON rc.RDB$CONSTRAINT_NAME = refc.RDB$CONSTRAINT_NAME
                LEFT JOIN RDB$RELATION_CONSTRAINTS rc2 ON rc2.RDB$CONSTRAINT_NAME = refc.RDB$CONST_NAME_UQ
                LEFT JOIN RDB$INDICES i2 ON i2.RDB$INDEX_NAME = rc2.RDB$INDEX_NAME
                LEFT JOIN RDB$INDEX_SEGMENTS s2 ON i2.RDB$INDEX_NAME = s2.RDB$INDEX_NAME
                    WHERE i.RDB$RELATION_NAME=\'' . $tableName . '\'
                      AND rc.RDB$CONSTRAINT_TYPE = \'PRIMARY KEY\'
                 ORDER BY s.RDB$FIELD_POSITION'
        )->AsObject();
    }

    /**
     * Gets the foreign keys for given table
     * @param string $tableName
     * @return array
     */
    final public function getForeignKeys(string $tableName): array
    {
        return $this->getConnection()->fetch(
            'SELECT rc.RDB$CONSTRAINT_NAME,
                          trim(s.RDB$FIELD_NAME) AS field_name,
                          rc.RDB$CONSTRAINT_TYPE AS constraint_type
                     FROM RDB$INDEX_SEGMENTS s
                LEFT JOIN RDB$INDICES i ON i.RDB$INDEX_NAME = s.RDB$INDEX_NAME
                LEFT JOIN RDB$RELATION_CONSTRAINTS rc ON rc.RDB$INDEX_NAME = s.RDB$INDEX_NAME
                LEFT JOIN RDB$REF_CONSTRAINTS refc ON rc.RDB$CONSTRAINT_NAME = refc.RDB$CONSTRAINT_NAME
                LEFT JOIN RDB$RELATION_CONSTRAINTS rc2 ON rc2.RDB$CONSTRAINT_NAME = refc.RDB$CONST_NAME_UQ
                LEFT JOIN RDB$INDICES i2 ON i2.RDB$INDEX_NAME = rc2.RDB$INDEX_NAME
                LEFT JOIN RDB$INDEX_SEGMENTS s2 ON i2.RDB$INDEX_NAME = s2.RDB$INDEX_NAME
                    WHERE i.RDB$RELATION_NAME=\'' . $tableName . '\'
                      AND rc.RDB$CONSTRAINT_TYPE = \'FOREIGN KEY\'
                 ORDER BY s.RDB$FIELD_POSITION'
        )->AsObject();
    }
}
