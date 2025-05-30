package com.example;

import java.util.List;
import java.util.ArrayList;

/**
 * A simple Java test file.
 */
public class Test {

    private String message = "Hello, World!";

    public static void main(String[] args) {
        Test tester = new Test();
        System.out.println(tester.getMessage());

        List<String> items = new ArrayList<>();
        items.add("Java");
        items.add("Syntax Highlighting");

        for (String item : items) {
            System.out.println("Item: " + item);
        }
    }

    public String getMessage() {
        return message;
    }
}
