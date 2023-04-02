"""Update fields for date operations

Revision ID: 4709d0bf479c
Revises: 88b3ad9b785e
Create Date: 2023-04-02 17:37:23.088879+00:00

"""
from alembic import op
import sqlalchemy as sa
import app
from sqlalchemy.dialects import postgresql

# revision identifiers, used by Alembic.
revision = '4709d0bf479c'
down_revision = '88b3ad9b785e'
branch_labels = None
depends_on = None


def upgrade():
    # ### commands auto generated by Alembic - please adjust! ###
    op.add_column('date_operation', sa.Column('date_data', postgresql.JSONB(astext_type=sa.Text()), nullable=True))
    op.drop_column('date_operation', 'target_date')
    # ### end Alembic commands ###


def downgrade():
    # ### commands auto generated by Alembic - please adjust! ###
    op.add_column('date_operation', sa.Column('target_date', sa.VARCHAR(), autoincrement=False, nullable=True))
    op.drop_column('date_operation', 'date_data')
    # ### end Alembic commands ###
