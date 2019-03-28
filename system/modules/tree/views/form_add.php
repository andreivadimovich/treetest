    <form method="POST" action="?add_category=true" id="category_add">
        <h3>Add category</h3>

        <table>
            <tr>
                <td>
                    Parent category
                </td>

                <td>
                    #categoryList#
                </td>
            </tr>

            <tr>
                <td>
                    Name of category
                </td>

                <td>
                    <input type="text" name="title" />
                </td>
            </tr>

            <tr>
                <td></td>
                <td align="right">
                    <input type="submit" value="Save" />
                </td>
            </tr>
        </table>
    </form>

    <hr />

    <form method="POST" action="?add_product=true" id="product_add">
        <h3>Add product</h3>

        <table>
            <tr>
                <td>
                    Product name
                </td>

                <td>
                    <input type="text" name="title" />
                </td>
            </tr>

            <tr>
                <td>
                    Category
                </td>

                <td>
                    #categoryForProduct#
                </td>
            </tr>

            <tr>
                <td>
                    Brand URL
                </td>

                <td>
                    <input type="text" name="url" />
                </td>
            </tr>

            <tr>
                <td>
                    Color
                </td>

                <td>
                    #productColors#
                </td>
            </tr>

            <tr>
                <td>
                    Price
                </td>

                <td>
                    <input type="text" name="price" />
                </td>
            </tr>

            <tr>
                <td></td>
                <td align="right">
                    <input type="submit" value="Save" />
                </td>
            </tr>
        </table>
    </form>
