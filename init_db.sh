#!/usr/bin/env bash

PROJECT_ROOT="$(cd -- "$(dirname -- "${BASH_SOURCE[0]}")/.." && pwd)"
sqlite3 "$PROJECT_ROOT/data/database.sqlite" < "$PROJECT_ROOT/lib/Database/database.sql"
