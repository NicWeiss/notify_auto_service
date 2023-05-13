"""init

Revision ID: 2b1b414a98da
Revises: a64644047135
Create Date: 2023-03-24 19:31:43.994259+00:00

"""
from alembic import op
import sqlalchemy as sa
import sqlalchemy_utils
import app


# revision identifiers, used by Alembic.
revision = '2b1b414a98da'
down_revision = 'a64644047135'
branch_labels = None
depends_on = None


def upgrade():
    # ### commands auto generated by Alembic - please adjust! ###
    op.create_table('date_operation',
                    sa.Column('id', sa.Integer(), nullable=False),
                    sa.Column('target_date', sa.Unicode(), nullable=True),
                    sa.Column('complete_date', sa.Unicode(), nullable=True),
                    sa.Column('type', sa.Unicode(), nullable=True),
                    sa.Column('worker_id', sa.Unicode(), nullable=True),
                    sa.Column('is_deleted', sa.Boolean(), nullable=True),
                    sa.PrimaryKeyConstraint('id')
                    )
    op.create_table('reg_code',
                    sa.Column('id', sa.Integer(), nullable=False),
                    sa.Column('code', sa.Unicode(), nullable=True),
                    sa.Column('email', sa.Unicode(), nullable=True),
                    sa.Column('expire_at', sqlalchemy_utils.types.arrow.ArrowType(), nullable=False),
                    sa.Column('is_deleted', sa.Boolean(), nullable=True),
                    sa.PrimaryKeyConstraint('id')
                    )
    op.create_table('system',
                    sa.Column('id', sa.Integer(), nullable=False),
                    sa.Column('name', sa.Unicode(), nullable=True),
                    sa.Column('is_disabled', sa.Boolean(), nullable=True),
                    sa.Column('is_system', sa.Boolean(), nullable=True),
                    sa.Column('help', sa.Unicode(), nullable=True),
                    sa.Column('type', sa.Unicode(), nullable=True),
                    sa.Column('is_deleted', sa.Boolean(), nullable=True),
                    sa.PrimaryKeyConstraint('id')
                    )
    op.create_table('acceptor',
                    sa.Column('id', sa.Integer(), nullable=False),
                    sa.Column('name', sa.Unicode(), nullable=True),
                    sa.Column('system_id', sa.Integer(), nullable=True),
                    sa.Column('user_id', sa.Integer(), nullable=True),
                    sa.Column('account', sa.Unicode(), nullable=True),
                    sa.Column('is_disabled', sa.Boolean(), nullable=True),
                    sa.Column('is_system', sa.Boolean(), nullable=True),
                    sa.Column('is_deleted', sa.Boolean(), nullable=True),
                    sa.ForeignKeyConstraint(['system_id'], ['system.id'], ),
                    sa.ForeignKeyConstraint(['user_id'], ['user.id'], ),
                    sa.PrimaryKeyConstraint('id')
                    )
    op.create_table('category',
                    sa.Column('id', sa.Integer(), nullable=False),
                    sa.Column('name', sa.Unicode(), nullable=True),
                    sa.Column('user_id', sa.Integer(), nullable=True),
                    sa.Column('is_hidden', sa.Boolean(), nullable=True),
                    sa.Column('is_deleted', sa.Boolean(), nullable=True),
                    sa.ForeignKeyConstraint(['user_id'], ['user.id'], ),
                    sa.PrimaryKeyConstraint('id')
                    )
    op.create_table('session',
                    sa.Column('id', sa.Integer(), nullable=False),
                    sa.Column('session', sa.Unicode(), nullable=True),
                    sa.Column('user_id', sa.Integer(), nullable=True),
                    sa.Column('client', sa.Unicode(), nullable=True),
                    sa.Column('location', sa.Unicode(), nullable=True),
                    sa.Column('expire_at', sqlalchemy_utils.types.arrow.ArrowType(), nullable=False),
                    sa.Column('is_deleted', sa.Boolean(), nullable=True),
                    sa.ForeignKeyConstraint(['user_id'], ['user.id'], ),
                    sa.PrimaryKeyConstraint('id')
                    )
    op.create_table('notify',
                    sa.Column('id', sa.Integer(), nullable=False),
                    sa.Column('name', sa.Unicode(), nullable=True),
                    sa.Column('user_id', sa.Integer(), nullable=True),
                    sa.Column('text', sa.Unicode(), nullable=True),
                    sa.Column('periodic', sa.Unicode(), nullable=True),
                    sa.Column('day_of_week', sa.Integer(), nullable=True),
                    sa.Column('date', sa.Unicode(), nullable=True),
                    sa.Column('time', sa.Unicode(), nullable=True),
                    sa.Column('is_disabled', sa.Boolean(), nullable=True),
                    sa.Column('category_id', sa.Integer(), nullable=True),
                    sa.Column('is_deleted', sa.Boolean(), nullable=True),
                    sa.ForeignKeyConstraint(['category_id'], ['category.id'], ),
                    sa.ForeignKeyConstraint(['user_id'], ['user.id'], ),
                    sa.PrimaryKeyConstraint('id')
                    )
    op.create_table('notify_acceptor',
                    sa.Column('id', sa.Integer(), nullable=False),
                    sa.Column('name', sa.Unicode(), nullable=True),
                    sa.Column('notify_id', sa.Integer(), nullable=True),
                    sa.Column('acceptor_id', sa.Integer(), nullable=True),
                    sa.Column('is_deleted', sa.Boolean(), nullable=True),
                    sa.ForeignKeyConstraint(['acceptor_id'], ['acceptor.id'], ),
                    sa.ForeignKeyConstraint(['notify_id'], ['notify.id'], ),
                    sa.PrimaryKeyConstraint('id')
                    )
    op.add_column('user', sa.Column('is_deleted', sa.Boolean(), nullable=True))
    # ### end Alembic commands ###


def downgrade():
    # ### commands auto generated by Alembic - please adjust! ###
    op.drop_column('user', 'is_deleted')
    op.drop_table('notify_acceptor')
    op.drop_table('notify')
    op.drop_table('session')
    op.drop_table('category')
    op.drop_table('acceptor')
    op.drop_table('system')
    op.drop_table('reg_code')
    op.drop_table('date_operation')
    # ### end Alembic commands ###