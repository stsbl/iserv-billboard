/**
 * Author:  Felix Jacobi
 * Created: 15.08.2016
 * License: http://gnu.org/licenses/gpl-3.0 GNU General Public License 
 */

CREATE TABLE billboard (
    id          SERIAL              PRIMARY KEY,
    title       VARCHAR(255),
    description TEXT,
    time        TIMESTAMPTZ(0) NOT NULL,
    updated_at  TIMESTAMPTZ(0) NOT NULL DEFAULT now(),
    category    INT     REFERENCES billboard_category(id)
                        ON DELETE SET NULL
                        ON UPDATE CASCADE,
    author      VARCHAR(255) REFERENCES users(act)
                             ON DELETE SET NULL
                             ON UPDATE CASCADE,
    visible     BOOLEAN
);

CREATE TABLE billboard_category (
    id          SERIAL      PRIMARY KEY,
    title       VARCHAR(255),
    description TEXT
);

GRANT USAGE, SELECT ON "billboard_id_seq", "billboard_category_id_seq" TO "symfony";
GRANT SELECT, INSERT, UPDATE, DELETE, TRUNCATE ON "billboard", "billboard_category" TO "symfony";