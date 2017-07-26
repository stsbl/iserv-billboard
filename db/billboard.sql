/**
 * Author:  Felix Jacobi
 * Created: 15.08.2016
 * License: https://opensource.org/licenses/MIT MIT license
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
    visible     BOOLEAN,
    closed      BOOLEAN
);

CREATE TABLE billboard_category (
    id          SERIAL      PRIMARY KEY,
    title       VARCHAR(255),
    description TEXT
);

CREATE TABLE billboard_images (
    id          SERIAL      PRIMARY KEY,
    image       FILE_IMAGE,
    description TEXT,
    author      VARCHAR(255) REFERENCES users(act)
                             ON DELETE SET NULL
                             ON UPDATE CASCADE,
    time        TIMESTAMPTZ(0) NOT NULL,
    updated_at  TIMESTAMPTZ(0) NOT NULL  DEFAULT now(),
    entry       INT     REFERENCES billboard(id)
                        ON DELETE SET NULL
                        ON UPDATE CASCADE
);

CREATE TABLE billboard_comments (
    id          SERIAL      PRIMARY KEY,
    title       VARCHAR(255) NOT NULL,
    content     TEXT NOT NULL,
    author      VARCHAR(255) REFERENCES users(act)
                             ON DELETE SET NULL
                             ON UPDATE CASCADE,
                      
    time        TIMESTAMPTZ(0) NOT NULL,
    entry       INT     REFERENCES billboard(id)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE
);

GRANT USAGE, SELECT ON "billboard_id_seq", "billboard_category_id_seq", "billboard_images_id_seq", "billboard_comments_id_seq" TO "symfony";
GRANT SELECT, INSERT, UPDATE, DELETE ON "billboard", "billboard_category", "billboard_images", "billboard_comments" TO "symfony";