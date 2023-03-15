/**
 * Author:  Felix Jacobi
 * Created: 15.08.2019
 * License: https://opensource.org/licenses/MIT MIT license
 */
CREATE TABLE billboard_category (
  id          SERIAL      PRIMARY KEY,
  title       TEXT,
  description TEXT
);

CREATE TABLE billboard (
    id          SERIAL              PRIMARY KEY,
    title       TEXT,
    description TEXT,
    time        TIMESTAMPTZ  NOT NULL,
    updated_at  TIMESTAMPTZ  NOT NULL DEFAULT now(),
    category    INT     REFERENCES billboard_category(id)
                        ON DELETE SET NULL
                        ON UPDATE CASCADE,
    author      TEXT REFERENCES users(act)
                             ON DELETE SET NULL
                             ON UPDATE CASCADE,
    visible     BOOLEAN         NOT NULL DEFAULT true,
    closed      BOOLEAN         NOT NULL DEFAULT false
);

CREATE TABLE billboard_images (
    id          SERIAL      PRIMARY KEY,
    image_uuid  UUID NOT NULL,
    image_name  TEXT NOT NULL,
    description TEXT,
    author      TEXT REFERENCES users(act)
                             ON DELETE SET NULL
                             ON UPDATE CASCADE,
    time        TIMESTAMPTZ NOT NULL,
    updated_at  TIMESTAMPTZ NOT NULL  DEFAULT now(),
    entry       INT     REFERENCES billboard(id)
                        ON DELETE SET NULL
                        ON UPDATE CASCADE
);

CREATE TABLE billboard_comments (
    id          SERIAL      PRIMARY KEY,
    title       TEXT NOT NULL,
    content     TEXT NOT NULL,
    author      TEXT REFERENCES users(act)
                             ON DELETE SET NULL
                             ON UPDATE CASCADE,
                      
    time        TIMESTAMPTZ NOT NULL,
    entry       INT     REFERENCES billboard(id)
                        ON DELETE CASCADE
                        ON UPDATE CASCADE
);

GRANT USAGE, SELECT ON "billboard_id_seq", "billboard_category_id_seq", "billboard_images_id_seq", "billboard_comments_id_seq" TO "symfony";
GRANT SELECT, INSERT, UPDATE, DELETE ON "billboard", "billboard_category", "billboard_images", "billboard_comments" TO "symfony";
